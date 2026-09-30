<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RestriccionesDelEsquemaTest extends TestCase
{
    use RefreshDatabase;

    private const string VIOLACION_CHECK = 'SQLSTATE[23514]';

    private int $sodaId;

    /** Crea la soda a la que pertenecen los datos de cada prueba. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->sodaId = DB::table('sodas')->insertGetId(['nombre' => 'Soda La Esquina']);
    }

    /** Un plato con los valores en los límites permitidos se guarda. */
    #[Test]
    public function un_plato_en_los_limites_permitidos_se_guarda(): void
    {
        DB::table('platos')->insert($this->plato(['precio' => 100, 'minutos_preparacion' => 1, 'porciones_disponibles' => 0]));
        DB::table('platos')->insert($this->plato(['nombre' => 'Casado especial', 'precio' => 100000, 'minutos_preparacion' => 120]));

        $this->assertSame(2, DB::table('platos')->count());
    }

    /**
     * La base de datos rechaza un plato con un valor fuera de rango.
     *
     * @param  array<string, int>  $cambios
     */
    #[Test]
    #[DataProvider('platosFueraDeRango')]
    public function la_base_rechaza_un_plato_fuera_de_rango(array $cambios): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage(self::VIOLACION_CHECK);

        DB::table('platos')->insert($this->plato($cambios));
    }

    /**
     * Valores de plato fuera de los rangos de la ERS.
     *
     * @return array<string, array{array<string, int>}>
     */
    public static function platosFueraDeRango(): array
    {
        return [
            'precio bajo ₡100' => [['precio' => 99]],
            'precio sobre ₡100 000' => [['precio' => 100001]],
            'cero minutos de preparación' => [['minutos_preparacion' => 0]],
            'más de 120 minutos de preparación' => [['minutos_preparacion' => 121]],
            'porciones negativas' => [['porciones_disponibles' => -1]],
        ];
    }

    /** Una soda no puede tener dos platos con el mismo nombre. */
    #[Test]
    public function una_soda_no_repite_el_nombre_de_un_plato(): void
    {
        DB::table('platos')->insert($this->plato());

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('SQLSTATE[23505]');

        DB::table('platos')->insert($this->plato());
    }

    /** La base de datos rechaza un estado de pedido fuera de los cinco valores. */
    #[Test]
    public function la_base_rechaza_un_estado_de_pedido_desconocido(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage(self::VIOLACION_CHECK);

        $this->crearPedido('pagado');
    }

    /**
     * La base de datos rechaza una línea con cantidad o precio fuera de rango.
     *
     * @param  array<string, int>  $cambios
     */
    #[Test]
    #[DataProvider('lineasFueraDeRango')]
    public function la_base_rechaza_una_linea_fuera_de_rango(array $cambios): void
    {
        $platoId = DB::table('platos')->insertGetId($this->plato());
        $pedidoId = $this->crearPedido('pendiente');

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage(self::VIOLACION_CHECK);

        DB::table('lineas_pedido')->insert([
            'pedido_id' => $pedidoId,
            'plato_id' => $platoId,
            'nombre_plato' => 'Casado de pollo',
            'precio_unitario' => 3200,
            'cantidad' => 1,
            ...$cambios,
        ]);
    }

    /**
     * Valores de línea fuera de los rangos de la ERS.
     *
     * @return array<string, array{array<string, int>}>
     */
    public static function lineasFueraDeRango(): array
    {
        return [
            'cantidad cero' => [['cantidad' => 0]],
            'más de 20 unidades' => [['cantidad' => 21]],
            'precio congelado en cero' => [['precio_unitario' => 0]],
        ];
    }

    /**
     * Datos de un plato válido, con los cambios indicados.
     *
     * @param  array<string, int|string>  $cambios
     * @return array<string, int|string|bool>
     */
    private function plato(array $cambios = []): array
    {
        return [
            'soda_id' => $this->sodaId,
            'nombre' => 'Casado de pollo',
            'precio' => 3200,
            'minutos_preparacion' => 15,
            'porciones_disponibles' => 20,
            'disponible' => true,
            ...$cambios,
        ];
    }

    /** Guarda un pedido con el estado indicado y devuelve su id. */
    private function crearPedido(string $estado): string
    {
        $id = (string) Str::uuid7();

        DB::table('pedidos')->insert([
            'id' => $id,
            'soda_id' => $this->sodaId,
            'cliente_id' => User::factory()->create()->id,
            'estado' => $estado,
            'realizado_en' => now(),
        ]);

        return $id;
    }
}
