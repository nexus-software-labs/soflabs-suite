<?php

namespace Database\Factories;

use App\Models\Core\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $lockerCode = 'SAL'.$this->faker->unique()->numberBetween(1000, 9999);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'email' => $this->faker->unique()->safeEmail(),
            'locker_code' => $lockerCode,
            'country' => $this->faker->randomElement(['SV', 'GT', 'HN', 'US']),
            'language' => $this->faker->randomElement(['es', 'en']),
            'secundary_email' => $this->faker->boolean(30) ? $this->faker->safeEmail() : null,
            'cedula_rnc' => $this->generateCedula(),
            'address' => $this->faker->address(),
            'birth_date' => $this->faker->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'phone' => $this->faker->numerify('####-####'),
            'home_phone' => $this->faker->boolean(40) ? $this->faker->numerify('####-####') : null,
            'office_phone' => $this->faker->boolean(30) ? $this->faker->numerify('####-####') : null,
            'fax' => $this->faker->boolean(10) ? $this->faker->numerify('####-####') : null,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => now(),
            'created_by' => User::factory(),
            'updated_by' => $this->faker->boolean(50) ? User::factory() : null,
        ];
    }

    /**
     * Genera una cédula realista para El Salvador
     */
    private function generateCedula(): string
    {
        return $this->faker->numerify('########-#');
    }

    /**
     * Cliente de El Salvador
     */
    public function salvadoran(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'SV',
            'language' => 'es',
            'cedula_rnc' => $this->generateCedula(),
            'locker_code' => 'SAL'.$this->faker->unique()->numberBetween(1000, 9999),
        ]);
    }

    /**
     * Cliente de USA
     */
    public function american(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'US',
            'language' => 'en',
            'cedula_rnc' => $this->faker->numerify('###-##-####'), // SSN format
            'locker_code' => 'USA'.$this->faker->unique()->numberBetween(1000, 9999),
        ]);
    }

    /**
     * Cliente con información completa
     */
    public function withCompleteInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'secundary_email' => $this->faker->safeEmail(),
            'home_phone' => $this->faker->numerify('####-####'),
            'office_phone' => $this->faker->numerify('####-####'),
            'fax' => $this->faker->numerify('####-####'),
        ]);
    }

    /**
     * Cliente con información mínima
     */
    public function withMinimalInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'secundary_email' => null,
            'home_phone' => null,
            'office_phone' => null,
            'fax' => null,
        ]);
    }
}
