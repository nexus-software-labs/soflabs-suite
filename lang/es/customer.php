<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Clientes',
    'model_label' => 'cliente',
    'plural_model_label' => 'Clientes',

    'sections' => [
        'user_info' => 'Usuario del portal',
        'customer_info' => 'Datos del cliente',
        'contact_info' => 'Contacto adicional',
    ],

    'fields' => [
        'user_name' => 'Nombre de usuario',
        'user_email' => 'Correo de acceso',
        'password' => 'Contraseña',
        'password_confirmation' => 'Confirmar contraseña',
        'locker_code' => 'Código de casillero',
        'country' => 'País',
        'language' => 'Idioma',
        'document_type' => 'Tipo de documento',
        'cedula_rnc' => 'Documento / RNC',
        'branch_id' => 'Sucursal',
        'birth_date' => 'Fecha de nacimiento',
        'secundary_email' => 'Correo secundario',
        'phone' => 'Móvil',
        'home_phone' => 'Teléfono casa',
        'office_phone' => 'Teléfono oficina',
        'fax' => 'Fax',
    ],

    'countries' => [
        'SV' => 'El Salvador',
        'GT' => 'Guatemala',
        'HN' => 'Honduras',
        'NI' => 'Nicaragua',
        'CR' => 'Costa Rica',
        'PA' => 'Panamá',
        'US' => 'Estados Unidos',
        'MX' => 'México',
    ],

    'languages' => [
        'es' => 'Español',
        'en' => 'Inglés',
    ],

    'placeholders' => [
        'select_country_document' => 'Selecciona país y tipo de documento',
        'document_number' => 'Número de documento',
    ],

    'helpers' => [
        'document_invalid' => 'El documento no cumple el formato :format',
        'document_format' => 'Formato esperado: :format',
    ],

    'table' => [
        'name' => 'Nombre',
        'email' => 'Correo',
        'locker_code' => 'Casillero',
        'phone' => 'Teléfono',
        'country' => 'País',
        'branch' => 'Sucursal',
        'verified' => 'Verificado',
        'cedula_rnc' => 'Documento',
        'registered' => 'Registro',
        'updated' => 'Actualizado',
    ],

    'tooltips' => [
        'verified_at' => 'Verificado el :date',
        'pending_verification' => 'Pendiente de verificación',
    ],

    'filters' => [
        'country' => 'País',
        'verification_status' => 'Verificación',
        'status' => 'Estado',
        'verified' => 'Verificado',
        'pending' => 'Pendiente',
        'has_locker' => 'Con casillero',
        'created_from' => 'Desde',
        'created_until' => 'Hasta',
    ],

    'actions' => [
        'verify' => 'Verificar cliente',
        'verify_modal_heading' => '¿Verificar este cliente?',
        'verify_modal_description' => 'Se marcará como verificado en el sistema.',
        'verify_modal_submit' => 'Verificar',
        'verify_notification_title' => 'Cliente verificado',
        'verify_notification_body' => 'El cliente quedó verificado correctamente.',
        'verify_success' => 'Verificación completada',
    ],
];
