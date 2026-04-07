<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintOrderRequest;
use App\Mail\PrintOrderCreatedMail;
use App\Models\Printing\PrintOrder;
use App\Services\CustomerService;
use App\Services\Payment\PaymentService;
use App\Services\PrintOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PrintOrderController extends Controller
{
    public function __construct(
        protected PrintOrderService $printOrderService,
        protected PaymentService $paymentService,
        protected CustomerService $customerService
    ) {}

    /**
     * Mostrar el formulario para crear un nuevo pedido
     */
    public function create()
    {
        $formData = $this->printOrderService->getFormData();

        return Inertia::render('Printing/CreatePrintOrder', $formData);
    }

    /**
     * Mostrar página de rastreo
     */
    public function track()
    {
        return Inertia::render('PrintOrders/OrderTracking');
    }

    /**
     * Buscar y mostrar un pedido
     */
    public function show($orderNumber)
    {
        $order = PrintOrder::withoutGlobalScopes()
            ->with(['history.creator', 'branch', 'promotion'])
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado',
                ], 404);
            }

            abort(404, 'Pedido no encontrado');
        }

        $orderData = $this->printOrderService->formatOrderData($order);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'orderData' => $orderData,
            ]);
        }

        $backUrl = null;
        if (auth()->check() && $order->user_id === auth()->id()) {
            $backUrl = route('print-orders.my-orders');
        }

        return Inertia::render('PrintOrders/OrderTracking', [
            'orderData' => $orderData,
            'showSearch' => false,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Mostrar mis pedidos (usuario autenticado)
     */
    public function myOrders(Request $request)
    {
        $filters = [
            'status' => $request->get('status', 'all'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
        ];

        return Inertia::render('PrintOrders/MyOrders', [
            'orders' => $this->printOrderService->getOrdersByUser(auth()->id(), 10, $filters),
            'stats' => $this->printOrderService->getOrderStats(auth()->id()),
            'filters' => $filters,
        ]);
    }

    /**
     * Guardar un nuevo pedido
     */
    public function store(StorePrintOrderRequest $request)
    {
        if (auth()->check() && ! $this->customerService->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes estar verificado para crear órdenes de impresión. Por favor, espera a que un administrador verifique tu cuenta.',
                'requires_verification' => true,
            ], 403);
        }

        try {
            $order = $this->printOrderService->createOrder($request->validated(), $request);

            try {
                Mail::to($order->customer_email)->send(new PrintOrderCreatedMail($order));
            } catch (\Throwable $th) {
                Log::error('Error enviando correo de confirmación de pedido: '.$th->getMessage());
            }

            // Si es una petición AJAX, retornar JSON con el ID de la orden para iniciar el pago
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => 'Pedido creado exitosamente',
                ]);
            }

            return redirect()->route('print-orders.success', ['orderNumber' => $order->order_number])
                ->with('success', '¡Pedido creado exitosamente!');

        } catch (\Exception $e) {
            Log::error('Error creando pedido: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return back()
                ->withErrors([
                    'error' => 'Hubo un error al crear el pedido. Por favor intenta de nuevo.',
                    'details' => $e->getMessage(),
                ])
                ->withInput();
        }
    }

    /**
     * Mostrar página de éxito
     */
    public function success($orderNumber)
    {
        $order = PrintOrder::where('order_number', $orderNumber)->first();

        if (! $order) {
            abort(404, 'Pedido no encontrado');
        }

        return Inertia::render('PrintOrders/Success', [
            'order' => $order,
        ]);
    }

    /**
     * Descargar archivo del pedido y actualizar downloaded_at
     */
    public function downloadFile($id)
    {
        $order = PrintOrder::findOrFail($id);

        // Obtener el primer archivo de la colección
        $media = $order->getFirstMedia('print-files');

        if (! $media) {
            abort(404, 'No se encontró archivo para este pedido');
        }

        // Actualizar downloaded_at siempre que se descarga
        $order->update(['downloaded_at' => now()]);

        // Retornar la descarga del archivo
        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Iniciar pago para una orden de impresión
     */
    public function initiatePayment(Request $request, int $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión para realizar el pago.'], 401);
        }

        $printOrder = PrintOrder::with('customer')->findOrFail($id);

        if ($printOrder->user_id !== auth()->id()) {
            abort(403);
        }

        if ($printOrder->isPaid()) {
            return response()->json([
                'error' => 'Esta orden ya ha sido pagada.',
            ], 400);
        }

        try {
            $gateway = $request->input('gateway', 'cybersource');
            if (! in_array($gateway, ['cybersource', 'cash', 'transfer'])) {
                return response()->json(['error' => 'Método de pago no válido.'], 400);
            }
            if ($gateway === 'transfer' && ! $request->hasFile('transfer_proof')) {
                return response()->json(['error' => 'Debes subir el comprobante de transferencia.'], 400);
            }

            $customer = $printOrder->customer;
            $total = $request->input('total', $printOrder->total);

            // Token para restaurar sesión al volver de CyberSource (evita pérdida de sesión)
            $restoreToken = Str::random(64);
            Cache::put("payment_restore_print:{$printOrder->id}", [
                'token' => $restoreToken,
                'user_id' => auth()->id(),
            ], now()->addHours(1));
            Cache::put("payment_restore_token_print:{$restoreToken}", [
                'user_id' => auth()->id(),
                'print_order_id' => $printOrder->id,
            ], now()->addHours(1));

            $options = [
                'user_id' => auth()->id(),
                'customer_name' => $printOrder->customer_name ?? $customer?->name ?? null,
                'customer_email' => $printOrder->customer_email ?? $customer?->email ?? null,
                'customer_phone' => $printOrder->customer_phone ?? $customer?->phone ?? null,
                'return_url' => route('print-orders.payment-success', $id),
                'cancel_url' => route('print-orders.payment-cancel', $id),
                'metadata' => [
                    'print_order_id' => $printOrder->id,
                    'order_number' => $printOrder->order_number,
                ],
                'subtotal_pre_alerta' => $printOrder->subtotal ?? null,
                'costo_envio' => $printOrder->delivery_cost ?? 0,
                'total' => $total,
            ];

            if ($gateway === 'transfer') {
                $options['transfer_proof'] = $request->file('transfer_proof');
                $options['transfer_reference'] = $request->input('transfer_reference');
                $options['transfer_notes'] = $request->input('transfer_notes');
            }

            $payment = $this->paymentService->initiate(
                payable: $printOrder,
                gateway: $gateway,
                amount: $total,
                options: $options
            );

            return response()->json([
                'success' => true,
                'redirect_url' => $payment->redirect_url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al iniciar el pago: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Página de éxito después del pago
     */
    public function paymentSuccess(Request $request, int $id)
    {
        $printOrder = PrintOrder::with('customer')->findOrFail($id);

        // Restaurar sesión si viene token (retorno desde CyberSource sin sesión)
        if (! auth()->check() && $request->has('restore_session')) {
            $restoreData = Cache::get("payment_restore_token_print:{$request->restore_session}");
            if ($restoreData && isset($restoreData['user_id']) && (int) $restoreData['print_order_id'] === (int) $id) {
                Auth::loginUsingId($restoreData['user_id']);
                Cache::forget("payment_restore_token_print:{$request->restore_session}");
                Cache::forget("payment_restore_print:{$id}");

                return redirect()->route('print-orders.payment-success', ['id' => $id])
                    ->with('success', session('success'))
                    ->with('error', session('error'));
            }
        }

        // Acceso permitido: usuario autenticado con permiso O URL firmada válida
        $isAuthorized = false;
        if (auth()->check()) {
            $isAuthorized = $printOrder->user_id === auth()->id();
        } elseif (URL::hasValidSignature($request)) {
            $isAuthorized = true;
        }

        if (! $isAuthorized) {
            abort(403, 'No tienes permiso para ver esta página. Por favor inicia sesión.');
        }

        $payment = $printOrder->latestPayment();

        return Inertia::render('PrintOrders/PaymentSuccess', [
            'printOrder' => $printOrder,
            'payment' => $payment,
        ]);
    }

    /**
     * Página de cancelación del pago
     */
    public function paymentCancel(Request $request, int $id)
    {
        $printOrder = PrintOrder::findOrFail($id);

        $isAuthorized = auth()->check() && $printOrder->user_id === auth()->id();
        $isAuthorized = $isAuthorized || URL::hasValidSignature($request);

        if (! $isAuthorized) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Pago cancelado. Inicia sesión para ver tu orden e intentar nuevamente.');
        }

        return redirect()->route('print-orders.show', $printOrder->order_number)
            ->with('info', 'Pago cancelado. Puedes intentar nuevamente cuando estés listo.');
    }
}
