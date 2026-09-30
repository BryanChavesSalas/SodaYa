<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\V1\Cocina\ActualizarPlatoRequest;
use App\Http\Requests\V1\Cocina\GuardarPlatoRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReglasDePlatoTest extends TestCase
{
    /** Un plato completo y dentro de los rangos es válido. */
    #[Test]
    public function un_plato_valido_cumple_las_reglas(): void
    {
        $this->assertTrue($this->cumple(new GuardarPlatoRequest, $this->plato()));
    }

    /**
     * Un plato nuevo con un campo inválido no cumple las reglas.
     *
     * @param  array<string, mixed>  $cambios
     */
    #[Test]
    #[DataProvider('platosInvalidos')]
    public function un_plato_con_un_campo_invalido_no_cumple_las_reglas(array $cambios): void
    {
        $this->assertFalse($this->cumple(new GuardarPlatoRequest, [...$this->plato(), ...$cambios]));
    }

    /**
     * Campos de plato inválidos según la ERS (RF-MEN-02).
     *
     * @return array<string, array{array<string, mixed>}>
     */
    public static function platosInvalidos(): array
    {
        return [
            'sin nombre' => [['nombre' => null]],
            'nombre de más de 120 caracteres' => [['nombre' => str_repeat('a', 121)]],
            'precio con decimales' => [['precio' => 2800.5]],
            'precio como texto' => [['precio' => '2800']],
            'precio bajo ₡100' => [['precio' => 99]],
            'precio sobre ₡100 000' => [['precio' => 100001]],
            'cero minutos de preparación' => [['minutos_preparacion' => 0]],
            'más de 120 minutos de preparación' => [['minutos_preparacion' => 121]],
            'porciones negativas' => [['porciones_disponibles' => -1]],
            'más de 500 porciones' => [['porciones_disponibles' => 501]],
            'disponible como texto' => [['disponible' => 'si']],
        ];
    }

    /** Editar un plato acepta enviar solo los campos que cambian. */
    #[Test]
    public function editar_acepta_solo_los_campos_que_cambian(): void
    {
        $solicitud = new ActualizarPlatoRequest;

        $this->assertTrue($this->cumple($solicitud, []));
        $this->assertTrue($this->cumple($solicitud, ['precio' => 3000]));
        $this->assertFalse($this->cumple($solicitud, ['precio' => 99]));
    }

    /**
     * Datos de un plato válido.
     *
     * @return array<string, mixed>
     */
    private function plato(): array
    {
        return [
            'nombre' => 'Casado de pollo',
            'precio' => 3200,
            'minutos_preparacion' => 15,
            'porciones_disponibles' => 20,
            'disponible' => true,
        ];
    }

    /**
     * Indica si los datos cumplen las reglas del Form Request.
     *
     * @param  array<string, mixed>  $datos
     */
    private function cumple(GuardarPlatoRequest|ActualizarPlatoRequest $solicitud, array $datos): bool
    {
        return Validator::make($datos, $solicitud->rules())->passes();
    }
}
