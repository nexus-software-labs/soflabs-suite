<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Core\Country;
use App\Models\Core\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    /**
     * Obtener customer por user_id
     */
    public function getByUserId(int $userId): ?Customer
    {
        return Customer::where('user_id', $userId)->first();
    }

    /**
     * Obtener customer del usuario autenticado
     */
    public function getCurrent(): ?Customer
    {
        if (! auth()->check()) {
            return null;
        }

        return $this->getByUserId(auth()->id());
    }

    /**
     * Sucursales disponibles para el customer (por código de país ISO-2 del customer y/o país del catálogo).
     */
    public function getBranches(?Customer $customer = null): Collection
    {
        $customer = $customer ?? $this->getCurrent();

        return Branch::query()
            ->withoutGlobalScopes()
            ->when($customer && $customer->country, function ($query) use ($customer) {
                $countryId = Country::query()
                    ->where('code', $customer->country)
                    ->value('id');

                $query->where(function ($q) use ($customer, $countryId) {
                    $q->where('country', $customer->country);
                    if ($countryId) {
                        $q->orWhere('country_id', $countryId);
                    }
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener direcciones del customer
     */
    public function getAddresses(?Customer $customer = null)
    {
        $customer = $customer ?? $this->getCurrent();

        return $customer
            ? $customer->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get()
            : collect([]);
    }

    /**
     * Obtener configuración del país del customer
     */
    public function getCountryConfig(?Customer $customer = null): ?array
    {
        $customer = $customer ?? $this->getCurrent();

        if (! $customer || ! $customer->country) {
            return null;
        }

        $country = Country::getShippingConfigByCode($customer->country);

        return $country ? $country->getShippingCalculationConfig() : null;
    }

    public function getSuggestedBranch(?Customer $customer = null): ?Branch
    {
        $customer = $customer ?? $this->getCurrent();

        if (! $customer || ! $customer->branch_id) {
            return null;
        }

        return Branch::withoutGlobalScopes()->find($customer->branch_id);
    }

    public function getSuggestedBranchId(?Customer $customer = null): ?string
    {
        $branch = $this->getSuggestedBranch($customer);

        return $branch?->id;
    }

    /**
     * Verificar si el customer está verificado
     */
    public function isVerified(?Customer $customer = null): bool
    {
        $customer = $customer ?? $this->getCurrent();

        return $customer && $customer->verified_at !== null;
    }

    /**
     * Obtener datos completos del customer para formularios
     *
     * @return array<string, mixed>
     */
    public function getFormData(?Customer $customer = null): array
    {
        $customer = $customer ?? $this->getCurrent();
        $branches = $this->getBranches($customer);
        $suggestedBranchId = $this->getSuggestedBranchId($customer);

        return [
            'customer' => $customer,
            'branches' => $branches,
            'customerAddresses' => $this->getAddresses($customer),
            'suggestedBranchId' => $suggestedBranchId,
            'countryConfig' => $this->getCountryConfig($customer),
            'isVerified' => $this->isVerified($customer),
        ];
    }
}
