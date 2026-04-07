<?php

namespace App\Filament\Admin\Resources\Customers\Actions;

use App\Imports\CustomersImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportCustomersAction
{
    public static function make(): Action
    {
        return Action::make('importCustomers')
            ->label(__('common.import.label_customers'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->modalHeading(__('common.import.modal_heading_customers'))
            ->modalDescription(__('common.import.modal_description_customers'))
            ->modalSubmitActionLabel(__('common.import.modal_submit'))
            ->modalWidth('lg')
            ->form([
                FileUpload::make('file')
                    ->label(__('common.import.file_label'))
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->required()
                    ->maxSize(10240)
                    ->helperText(__('common.import.file_helper'))
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private'),
            ])
            ->action(function (array $data) {
                try {
                    if (empty($data['file'])) {
                        throw new \Exception(__('common.import.exception_no_file'));
                    }

                    $filePath = null;
                    $fileToDelete = null;

                    // Intentar diferentes formas de obtener la ruta del archivo
                    if (str_starts_with($data['file'], '/') || str_starts_with($data['file'], storage_path())) {
                        $filePath = $data['file'];
                    } elseif (Storage::disk('local')->exists($data['file'])) {
                        $filePath = Storage::disk('local')->path($data['file']);
                        $fileToDelete = $data['file'];
                    } elseif (Storage::disk('local')->exists('imports/'.$data['file'])) {
                        $filePath = Storage::disk('local')->path('imports/'.$data['file']);
                        $fileToDelete = 'imports/'.$data['file'];
                    } elseif (file_exists(storage_path('app/private/'.$data['file']))) {
                        $filePath = storage_path('app/private/'.$data['file']);
                        $fileToDelete = 'private/'.$data['file'];
                    } elseif (file_exists(storage_path('app/imports/'.$data['file']))) {
                        $filePath = storage_path('app/imports/'.$data['file']);
                        $fileToDelete = 'imports/'.$data['file'];
                    } elseif (file_exists(storage_path('app/'.$data['file']))) {
                        $filePath = storage_path('app/'.$data['file']);
                        $fileToDelete = $data['file'];
                    } else {
                        $filePath = $data['file'];
                    }

                    if (! $filePath || ! file_exists($filePath)) {
                        Log::error('Error al encontrar archivo de importación', [
                            'file_data' => $data['file'],
                            'storage_path' => storage_path('app'),
                        ]);

                        throw new \Exception(__('common.import.exception_file_not_found', ['path' => $data['file'] ?? __('common.import.path_none')]));
                    }

                    // Aumentar tiempo de ejecución a 5 minutos para archivos grandes
                    set_time_limit(-1);
                    ini_set('max_execution_time', '-1');
                    ini_set('memory_limit', '-1');

                    // Usar Maatwebsite Excel para importar
                    $import = new CustomersImport;

                    DB::beginTransaction();
                    try {
                        Excel::import($import, $filePath);
                        DB::commit();

                        // Obtener estadísticas
                        $stats = $import->getStats();

                        // Eliminar el archivo después de procesarlo
                        try {
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                            if ($fileToDelete && Storage::disk('local')->exists($fileToDelete)) {
                                Storage::disk('local')->delete($fileToDelete);
                            }
                        } catch (\Exception $e) {
                            Log::warning('No se pudo eliminar el archivo de importación', [
                                'file' => $filePath,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        $message = __('common.import.body_created_customers', ['count' => $stats['created']]);
                        if ($stats['existed'] > 0) {
                            $message .= __('common.import.body_existed_customers', ['count' => $stats['existed']]);
                        }
                        if (! empty($stats['errors'])) {
                            $message .= __('common.import.body_errors', ['count' => count($stats['errors'])]);
                        }

                        Notification::make()
                            ->title(__('common.import.success_title'))
                            ->body($message)
                            ->success()
                            ->send();

                        if (! empty($stats['errors'])) {
                            Log::warning('Errores en importación de clientes', ['errors' => $stats['errors']]);
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('common.import.error_title'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
