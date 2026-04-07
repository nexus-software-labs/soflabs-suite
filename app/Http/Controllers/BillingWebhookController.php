<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Core\Payment;
use App\Models\Subscriptions\TenantSubscription;
use App\Services\Payment\PaymentService;
use App\Services\Subscriptions\SubscriptionAlertService;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly SubscriptionService $subscriptionService,
        private readonly SubscriptionAlertService $alertService,
    ) {}

    public function renewal(Request $request, string $gateway): JsonResponse
    {
        $secret = (string) config('services.billing.webhook_secret', '');
        if (filled($secret) && $request->header('X-Billing-Webhook-Secret') !== $secret) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->all();

        try {
            $payment = $this->resolvePaymentFromPayload($payload, $gateway);
            if ($payment === null) {
                return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
            }

            $this->subscriptionService->handlePaymentStatusFromGateway($payment);

            $this->alertService->notify(
                type: 'renewal_webhook_received',
                title: 'Webhook de renovación procesado',
                message: 'Se actualizó el estado de pago/suscripción desde webhook.',
                subscription: $payment->paymentable_type === TenantSubscription::class
                    ? TenantSubscription::query()->find($payment->paymentable_id)
                    : null,
                level: 'info',
                context: [
                    'gateway' => $gateway,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference_number,
                ],
            );

            return response()->json([
                'ok' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Webhook processing failed',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function resolvePaymentFromPayload(array $payload, string $gateway): ?Payment
    {
        if (isset($payload['reference_number']) || isset($payload['req_reference_number'])) {
            $payment = $this->paymentService->handleCallback($gateway, $payload);

            return $payment->fresh();
        }

        $paymentId = $payload['payment_id'] ?? null;
        if (! is_numeric($paymentId)) {
            return null;
        }

        $payment = Payment::query()->find((int) $paymentId);
        if ($payment === null) {
            return null;
        }

        $status = (string) ($payload['status'] ?? '');
        if ($status === Payment::STATUS_COMPLETED) {
            $payment->markAsCompleted($payload);
        } elseif ($status === Payment::STATUS_FAILED) {
            $payment->markAsFailed(
                reasonCode: (string) ($payload['reason_code'] ?? ''),
                reasonMessage: (string) ($payload['reason_message'] ?? 'Webhook failure'),
                gatewayResponse: $payload,
            );
        }

        return $payment->fresh();
    }
}
