<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Pagos',
    'model_label' => 'pago',
    'plural_model_label' => 'Pagos',

    'confirm_received' => 'Confirmar recepción',
    'confirm_modal' => [
        'heading' => 'Confirmar pago recibido',
        'description' => '¿Marcar este pago como recibido y completado?',
        'submit_label' => 'Confirmar',
        'notification_title' => 'Pago actualizado',
    ],

    'table' => [
        'reference' => 'Referencia',
        'type' => 'Tipo',
        'method' => 'Método',
        'amount' => 'Monto',
        'status' => 'Estado',
        'customer' => 'Cliente',
        'date' => 'Fecha',
    ],

    'paymentable_types' => [
        'pre_alert' => 'Pre-alerta',
        'print_order' => 'Pedido de impresión',
    ],

    'gateways' => [
        'cybersource' => 'Cybersource',
        'transfer' => 'Transferencia',
        'cash' => 'Efectivo',
    ],

    'statuses' => [
        'pending' => 'Pendiente',
        'processing' => 'Procesando',
        'completed' => 'Completado',
        'failed' => 'Fallido',
        'cancelled' => 'Cancelado',
        'refunded' => 'Reembolsado',
    ],

    'filters' => [
        'method' => 'Método',
        'status' => 'Estado',
    ],

    'infolist' => [
        'section_payment_info' => 'Información del pago',
        'reference' => 'Referencia',
        'method' => 'Método',
        'amount' => 'Monto',
        'status' => 'Estado',
        'customer' => 'Cliente',
        'email' => 'Correo',
        'transfer_reference' => 'Referencia de transferencia',
        'transfer_notes' => 'Notas',
        'created_at' => 'Creado',
        'completed_at' => 'Completado',
        'section_transfer_proof' => 'Comprobante',
        'proof_no_uploaded' => 'Sin comprobante',
        'proof_alt' => 'Comprobante de pago',
        'proof_view_pdf' => 'Ver PDF',
        'section_order' => 'Pedido relacionado',
        'id' => 'ID',
        'prealert_id' => 'Pre-alerta #:track',
        'order_id' => 'Pedido #:number',
    ],
];
