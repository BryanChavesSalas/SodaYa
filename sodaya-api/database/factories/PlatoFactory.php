<?php

namespace Database\Factories;

use App\Models\Plato;
use App\Models\Soda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plato>
 */
class PlatoFactory extends Factory
{
    private const array PLATOS = ['Casado', 'Gallo pinto', 'Chifrijo', 'Arroz con pollo', 'Olla de carne', 'Sopa negra', 'Tamal', 'Fresco de cas'];

    /**
     * Plato disponible de una soda, con precio en colones enteros.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'soda_id' => Soda::factory(),
            'nombre' => fake()->randomElement(self::PLATOS).' '.fake()->unique()->numerify('###'),
            'precio' => fake()->numberBetween(10, 60) * 100,
            'minutos_preparacion' => fake()->numberBetween(5, 45),
            'porciones_disponibles' => fake()->numberBetween(5, 40),
            'disponible' => true,
        ];
    }

    /** Plato que la soda sacó del menú. */
    public function noDisponible(): static
    {
        return $this->state(['disponible' => false]);
    }

    /** Plato sin porciones para hoy. */
    public function agotado(): static
    {
        return $this->state(['porciones_disponibles' => 0]);
    }
}
