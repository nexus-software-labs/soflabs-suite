<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePrintOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|File>>
     */
    public function rules(): array
    {
        return [
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
            'customer.notes' => ['nullable', 'string', 'max:2000'],

            'config' => ['required', 'array'],
            'config.printType' => ['required', 'string', 'in:bw,color'],
            'config.paperSize' => ['required', 'string'],
            'config.paperType' => ['nullable', 'string'],
            'config.orientation' => ['required', 'string'],
            'config.copies' => ['required', 'integer', 'min:1'],
            'config.doubleSided' => ['sometimes', 'boolean'],
            'config.binding' => ['sometimes', 'boolean'],
            'config.pageRange' => ['nullable', 'string'],

            'delivery' => ['required', 'array'],
            'delivery.method' => ['required', 'string', 'in:pickup,delivery'],
            'delivery.branch_id' => ['nullable', 'string', 'exists:branches,id'],
            'delivery.customerAddressId' => ['nullable', 'integer'],
            'delivery.address' => ['nullable', 'string'],
            'delivery.phone' => ['nullable', 'string'],
            'delivery.notes' => ['nullable', 'string'],

            'promotion_id' => ['nullable', 'integer', 'exists:promotions,id'],
            'coupon_code' => ['nullable', 'string', 'max:64'],

            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:51200'],
        ];
    }
}
