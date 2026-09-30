<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\V1\RealizarPedidoRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReglasDePedidoTest extends TestCase
{
    /** Un pedido con platos y cantidades en rango es válido. */
    #[Test]
    public function un_pedido_valido_cumple_las_reglas(): void
    {
        $this->assertTrue($this->validador($this->pedido())->passes());
    }

    /**
     * Un pedido con la forma incorrecta no cumple las reglas.
     *
     * @param  array<string, mixed>  $datos
     */
    #[Test]
    #[DataProvider('pedidosInvalidos')]
    public function un_pedido_con_forma_incorrecta_no_cumple_las_reglas(array $datos): void
    {
        $this->assertFalse($this->validador($datos)->passes());
    }

    /**
     * Pedidos con la forma incorrecta según la ERS (RF-PED-01).
     *
     * @return array<string, array{array<string, mixed>}>
     */
    public static function pedidosInvalidos(): array
    {
        $linea = ['plato_id' => 1, 'cantidad' => 1];

        return [
            'sin líneas' => [[]],
            'líneas vacías' => [['lineas' => []]],
            'más de 20 líneas' => [['lineas' => array_map(fn (int $id): array => ['plato_id' => $id, 'cantidad' => 1], range(1, 21))]],
            'plato repetido' => [['lineas' => [$linea, $linea]]],
            'línea sin plato' => [['lineas' => [['cantidad' => 1]]]],
            'cantidad cero' => [['lineas' => [['plato_id' => 1, 'cantidad' => 0]]]],
            'más de 20 unidades' => [['lineas' => [['plato_id' => 1, 'cantidad' => 21]]]],
            'cantidad como texto' => [['lineas' => [['plato_id' => 1, 'cantidad' => '2']]]],
        ];
    }

    /** Los datos validados descartan el total que envía el navegador. */
    #[Test]
    public function los_datos_validados_descartan_el_total_del_navegador(): void
    {
        $validados = $this->validador([...$this->pedido(), 'total' => 1])->validated();

        $this->assertArrayNotHasKey('total', $validados);
    }

    /**
     * Datos de un pedido válido.
     *
     * @return array<string, mixed>
     */
    private function pedido(): array
    {
        return ['lineas' => [['plato_id' => 3, 'cantidad' => 2], ['plato_id' => 5, 'cantidad' => 1]]];
    }

    /**
     * Crea el validador con las reglas del Form Request del pedido.
     *
     * @param  array<string, mixed>  $datos
     */
    private function validador(array $datos): \Illuminate\Validation\Validator
    {
        return Validator::make($datos, (new RealizarPedidoRequest)->rules());
    }
}
