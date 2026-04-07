<?php

declare(strict_types=1);

return [
    'navigation_groups' => [
        'gestion_pedidos' => 'Gestión de pedidos',
        'configuracion' => 'Configuración',
    ],
    'import' => [
        'label_customers' => 'Importar clientes',
        'modal_heading_customers' => 'Importar clientes desde CSV',
        'modal_description_customers' => 'Sube un archivo CSV con las columnas requeridas.',
        'modal_submit' => 'Importar',
        'file_label' => 'Archivo',
        'file_helper' => 'Formato CSV, máximo según configuración del servidor.',
        'exception_no_file' => 'No se seleccionó ningún archivo.',
        'exception_file_not_found' => 'No se encontró el archivo: :path',
        'path_none' => '(vacío)',
        'body_created_customers' => 'Creados: :count. ',
        'body_existed_customers' => 'Ya existían: :count. ',
        'body_errors' => 'Errores: :count. ',
        'success_title' => 'Importación finalizada',
        'error_title' => 'Error al importar',
    ],
];
