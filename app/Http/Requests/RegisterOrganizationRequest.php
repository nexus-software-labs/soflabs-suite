<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegisterOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:255', 'regex:/^[a-z]+(-[a-z]+)*$/', 'unique:tenants,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'plan_id' => ['required', 'exists:plans,id'],
            'db_mode' => ['sometimes', 'string', 'in:shared,schema,dedicated'],
            'active_modules' => ['nullable', 'array'],
            'active_modules.*' => ['string', 'in:inventory,packages,printing'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly'],
            'billing_gateway' => ['sometimes', 'string', 'in:cybersource,transfer,cash'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $planId = $this->input('plan_id');
            if (! is_string($planId) && ! is_int($planId)) {
                return;
            }

            $plan = Plan::query()->find($planId);
            if ($plan === null) {
                return;
            }

            $allowed = $plan->modules;
            if (! is_array($allowed) || $allowed === []) {
                return;
            }

            $selected = $this->input('active_modules', []);
            $selected = is_array($selected) ? $selected : [];

            foreach ($selected as $module) {
                if (! in_array($module, $allowed, true)) {
                    $validator->errors()->add(
                        'active_modules',
                        __('Uno o más módulos no están incluidos en el plan seleccionado.'),
                    );

                    return;
                }
            }
        });
    }
}
