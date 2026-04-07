<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Branch;

/**
 * Acceso tipado a claves del JSON `settings` en modelos como {@see Branch}.
 */
trait HasConfiguration
{
    /**
     * @param  array<string, mixed>|null  $default
     * @return array<string, mixed>|mixed|null
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        $settings = $this->getAttribute('settings');

        if (! is_array($settings)) {
            return $key === null ? [] : $default;
        }

        if ($key === null) {
            return $settings;
        }

        return $settings[$key] ?? $default;
    }
}
