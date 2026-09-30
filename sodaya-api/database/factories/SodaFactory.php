<?php

namespace Database\Factories;

use App\Models\Soda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Soda>
 */
class SodaFactory extends Factory
{
    /**
     * Soda ficticia con nombre y teléfono de Costa Rica.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Soda '.fake()->unique()->lastName(),
            'telefono' => fake()->numerify('24##-####'),
        ];
    }
}
