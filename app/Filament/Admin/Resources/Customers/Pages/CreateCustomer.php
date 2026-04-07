<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Models\Core\Customer;
use App\Models\Core\CustomerAddress;
use App\Models\User;
use App\Services\GeoNamesService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function handleRecordCreation(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            // Separar datos del usuario y del cliente
            $userData = [
                'name' => $data['user_name'] ?? $data['name'] ?? '',
                'email' => $data['user_email'] ?? $data['email'] ?? '',
                'password' => Hash::make($data['password']),
            ];

            // Crear usuario primero
            $user = User::create($userData);
            $user->assignRole('customer');

            // Obtener coordenadas si no están presentes
            if (empty($data['latitude']) || empty($data['longitude'])) {
                $coordinates = GeoNamesService::getCoordinates(
                    $data['country'] ?? 'SV',
                    $data['region'] ?? '',
                    $data['city'] ?? '',
                    $data['locality'] ?? null
                );

                if ($coordinates) {
                    $data['latitude'] = $coordinates['latitude'];
                    $data['longitude'] = $coordinates['longitude'];
                }
            }

            // Preparar datos del cliente
            $customerData = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'locker_code' => $data['locker_code'] ?? null,
                'cedula_rnc' => $data['cedula_rnc'] ?? null,
                'document_type' => $data['document_type'] ?? null,
                'country' => $data['country'] ?? 'SV',
                'branch_id' => $data['branch_id'] ?? null,
                'language' => $data['language'] ?? 'es',
                'secundary_email' => $data['secundary_email'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'phone' => $data['phone'] ?? null,
                'home_phone' => $data['home_phone'] ?? null,
                'office_phone' => $data['office_phone'] ?? null,
                'fax' => $data['fax'] ?? null,
            ];

            // Crear cliente
            $customer = Customer::create($customerData);

            // Crear dirección
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'name' => 'Principal',
                'country' => $data['country'] ?? 'SV',
                'region' => $data['region'] ?? '',
                'city' => $data['city'] ?? '',
                'locality' => $data['locality'] ?? null,
                'address' => $data['address'] ?? '',
                'references' => $data['references'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_default' => true,
            ]);

            return $customer;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
