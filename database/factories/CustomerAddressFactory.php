<?php

namespace Database\Factories;

use App\Models\Core\Customer;
use App\Models\Core\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    protected $model = CustomerAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $addressTypes = [
            'Casa',
            'Oficina',
            'Trabajo',
            'Casa de Padres',
            'Casa de Playa',
            'Apartamento',
            'Negocio',
        ];

        $cities = [
            'San Salvador',
            'Santa Ana',
            'San Miguel',
            'Sonsonate',
            'La Libertad',
            'Usulután',
            'Ahuachapán',
            'La Unión',
            'Antiguo Cuscatlán',
            'Santa Tecla',
        ];

        $localities = [
            'Colonia Escalón',
            'Colonia San Benito',
            'Colonia Flor Blanca',
            'Residencial Los Héroes',
            'Colonia Miramonte',
            'San Benito',
            'Zona Rosa',
            'Centro Histórico',
            'Colonia Centroamérica',
            'Jardines de Guadalupe',
        ];

        $references = [
            'Frente al parque',
            'A dos cuadras del supermercado',
            'Contiguo a la gasolinera Shell',
            'Frente a Pollo Campero',
            'Al lado de la iglesia',
            'Cerca del centro comercial',
            'Contiguo a farmacia',
            'Segunda casa a mano derecha',
            'Portón verde',
            'Casa de dos plantas',
        ];

        $country = $this->faker->randomElement(['SV', 'GT', 'HN', 'US']);

        $latitude = match ($country) {
            'SV' => $this->faker->latitude(13.5, 14.5),
            'GT' => $this->faker->latitude(14, 15),
            'HN' => $this->faker->latitude(13, 16),
            'US' => $this->faker->latitude(25, 48),
        };

        $longitude = match ($country) {
            'SV' => $this->faker->longitude(-90, -87.5),
            'GT' => $this->faker->longitude(-92, -88),
            'HN' => $this->faker->longitude(-89, -83),
            'US' => $this->faker->longitude(-125, -66),
        };

        return [
            'customer_id' => Customer::factory(),
            'name' => $this->faker->randomElement($addressTypes),
            'country' => $country,
            'region' => $this->faker->state(),
            'city' => $this->faker->randomElement($cities),
            'locality' => $this->faker->randomElement($localities),
            'address' => $this->faker->streetAddress(),
            'references' => $this->faker->boolean(70)
                ? $this->faker->randomElement($references)
                : null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'phone' => $this->faker->boolean(80) ? $this->faker->numerify('####-####') : null,
            'is_default' => false, // Se establece manualmente después
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
            'created_by' => User::factory(),
            'updated_by' => $this->faker->boolean(40) ? User::factory() : null,
        ];
    }

    /**
     * Dirección por defecto
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Dirección de casa
     */
    public function home(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Casa',
        ]);
    }

    /**
     * Dirección de oficina
     */
    public function office(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Oficina',
        ]);
    }

    /**
     * Dirección en El Salvador
     */
    public function inElSalvador(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'SV',
            'region' => $this->faker->randomElement([
                'San Salvador',
                'Santa Ana',
                'San Miguel',
                'La Libertad',
            ]),
            'city' => $this->faker->randomElement([
                'San Salvador',
                'Santa Ana',
                'San Miguel',
                'Santa Tecla',
            ]),
            'latitude' => $this->faker->latitude(13.5, 14.5),
            'longitude' => $this->faker->longitude(-90, -87.5),
        ]);
    }

    /**
     * Dirección con referencias detalladas
     */
    public function withDetailedReferences(): static
    {
        return $this->state(fn (array $attributes) => [
            'references' => 'Casa de dos plantas, portón negro, frente al parque central. Timbre en puerta lateral.',
        ]);
    }

    /**
     * Sin coordenadas GPS
     */
    public function withoutCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
