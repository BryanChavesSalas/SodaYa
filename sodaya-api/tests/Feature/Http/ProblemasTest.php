<?php

namespace Tests\Feature\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use SodaYa\Compartido\Domain\ErrorDeDominio;
use Tests\TestCase;

final class ProblemasTest extends TestCase
{
    private const string TIPOS = 'https://api.sodaya.test/problemas/';

    /** Una ruta inexistente responde 404 con el instance igual al X-Request-Id. */
    #[Test]
    public function una_ruta_inexistente_responde_un_problema_con_su_identificador(): void
    {
        $respuesta = $this->getJson('/api/v1/ruta-que-no-existe');

        $respuesta->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertExactJson([
                'type' => self::TIPOS.'no-encontrado',
                'title' => 'Recurso no encontrado',
                'status' => 404,
                'detail' => 'El recurso solicitado no existe.',
                'instance' => 'urn:uuid:'.$respuesta->headers->get('X-Request-Id'),
            ]);
    }

    /** Un método que la ruta no acepta responde 405. */
    #[Test]
    public function un_metodo_no_permitido_responde_405(): void
    {
        Route::get('/api/v1/prueba', fn () => 'ok');

        $this->deleteJson('/api/v1/prueba')
            ->assertStatus(405)
            ->assertJsonPath('type', self::TIPOS.'metodo-no-permitido');
    }

    /** Los datos inválidos responden 422 con los errores por campo. */
    #[Test]
    public function los_datos_invalidos_responden_422_con_los_errores_por_campo(): void
    {
        Route::post('/api/v1/prueba', fn (Request $request) => $request->validate(['nombre' => 'required']));

        $this->postJson('/api/v1/prueba')
            ->assertUnprocessable()
            ->assertJsonPath('type', self::TIPOS.'datos-invalidos')
            ->assertJsonPath('detail', 'Uno o más campos no cumplen las reglas.')
            ->assertJsonStructure(['errors' => ['nombre']]);
    }

    /** Una ruta protegida sin token responde 401 e indica el esquema Bearer. */
    #[Test]
    public function sin_token_responde_401_con_el_esquema_bearer(): void
    {
        Route::get('/api/v1/prueba', fn () => 'ok')->middleware('auth:sanctum');

        $this->getJson('/api/v1/prueba')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertJsonPath('type', self::TIPOS.'no-autenticado');
    }

    /** Una regla de negocio incumplida responde con el código de su tipo y su mensaje. */
    #[Test]
    public function un_error_de_dominio_responde_con_el_codigo_de_su_tipo(): void
    {
        Route::get('/api/v1/prueba', fn () => throw $this->errorDeDominio('porciones-agotadas', 'Quedan 2 porciones de Casado.'));

        $this->getJson('/api/v1/prueba')
            ->assertConflict()
            ->assertJsonPath('type', self::TIPOS.'porciones-agotadas')
            ->assertJsonPath('title', 'Porciones agotadas')
            ->assertJsonPath('detail', 'Quedan 2 porciones de Casado.');
    }

    /** Un código de dominio que no está en el catálogo responde como conflicto genérico. */
    #[Test]
    public function un_codigo_fuera_del_catalogo_responde_como_conflicto(): void
    {
        Route::get('/api/v1/prueba', fn () => throw $this->errorDeDominio('regla-nueva', 'No se puede.'));

        $this->getJson('/api/v1/prueba')
            ->assertConflict()
            ->assertJsonPath('type', self::TIPOS.'conflicto');
    }

    /** Superar el límite de solicitudes responde 429 con Retry-After. */
    #[Test]
    public function superar_el_limite_responde_429_con_retry_after(): void
    {
        Route::get('/api/v1/prueba', fn () => 'ok')->middleware('throttle:1,1');

        $this->getJson('/api/v1/prueba')->assertOk();
        $this->getJson('/api/v1/prueba')
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertJsonPath('type', self::TIPOS.'demasiadas-solicitudes');
    }

    /** En producción un error interno no revela SQL, clases ni trazas. */
    #[Test]
    public function en_produccion_un_error_interno_no_revela_detalles(): void
    {
        config(['app.debug' => false]);
        Route::get('/api/v1/prueba', fn () => DB::select('select * from tabla_que_no_existe'));

        $respuesta = $this->getJson('/api/v1/prueba')
            ->assertInternalServerError()
            ->assertJsonPath('type', self::TIPOS.'error-interno')
            ->assertJsonMissingPath('debug')
            ->assertJsonMissingPath('trace');

        $this->assertStringNotContainsString('tabla_que_no_existe', (string) $respuesta->getContent());
        $this->assertStringNotContainsString('SQLSTATE', (string) $respuesta->getContent());
        $this->assertStringNotContainsString('QueryException', (string) $respuesta->getContent());
    }

    /** En desarrollo el error interno agrega su origen, pero nunca la traza. */
    #[Test]
    public function en_desarrollo_el_error_interno_agrega_su_origen_sin_traza(): void
    {
        config(['app.debug' => true]);
        Route::get('/api/v1/prueba', fn () => throw new \RuntimeException('Falla de prueba'));

        $this->getJson('/api/v1/prueba')
            ->assertInternalServerError()
            ->assertJsonPath('debug.excepcion', \RuntimeException::class)
            ->assertJsonPath('debug.mensaje', 'Falla de prueba')
            ->assertJsonMissingPath('trace');
    }

    /** Crea un error de dominio con el código y el mensaje indicados. */
    private function errorDeDominio(string $codigo, string $mensaje): ErrorDeDominio
    {
        return new class($codigo, $mensaje) extends ErrorDeDominio
        {
            /** Recibe el código junto con el mensaje. */
            public function __construct(private readonly string $clave, string $mensaje)
            {
                parent::__construct($mensaje);
            }

            /** Código del catálogo de la regla incumplida. */
            public function codigo(): string
            {
                return $this->clave;
            }
        };
    }
}
