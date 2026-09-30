<?php

namespace Tests\Feature\Api\V1;

use App\Models\Plato;
use App\Models\Soda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MenuPublicoTest extends TestCase
{
    use RefreshDatabase;

    private Soda $soda;

    /** Crea la soda cuyo menú se consulta. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->soda = Soda::factory()->create();
    }

    /** El menú lista solo los platos disponibles de esa soda, ordenados por nombre. */
    #[Test]
    public function el_menu_lista_solo_los_platos_disponibles_de_la_soda(): void
    {
        Plato::factory()->for($this->soda)->create(['nombre' => 'Gallo pinto']);
        Plato::factory()->for($this->soda)->create(['nombre' => 'Casado']);
        Plato::factory()->for($this->soda)->noDisponible()->create(['nombre' => 'Tamal']);
        Plato::factory()->create(['nombre' => 'Chifrijo']);

        $this->getJson("/api/v1/sodas/{$this->soda->id}/platos")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.attributes.nombre', 'Casado')
            ->assertJsonPath('data.1.attributes.nombre', 'Gallo pinto');
    }

    /** Cada plato responde en JSON:API con precio en colones enteros y sin campos internos. */
    #[Test]
    public function cada_plato_responde_solo_sus_atributos_publicos(): void
    {
        $plato = Plato::factory()->for($this->soda)->create(['precio' => 2800]);

        $this->getJson("/api/v1/sodas/{$this->soda->id}/platos")
            ->assertOk()
            ->assertExactJsonStructure([
                'data' => [['id', 'type', 'attributes' => ['nombre', 'precio', 'moneda', 'minutos_preparacion', 'porciones_disponibles'], 'links' => ['self']]],
                'links',
                'meta',
            ])
            ->assertJsonPath('data.0.type', 'platos')
            ->assertJsonPath('data.0.attributes.precio', 2800)
            ->assertJsonPath('data.0.attributes.moneda', 'CRC')
            ->assertJsonPath('data.0.links.self', route('v1.sodas.platos.show', [$this->soda, $plato]));
    }

    /** El menú se pagina de 15 en 15. */
    #[Test]
    public function el_menu_se_pagina_de_15_en_15(): void
    {
        Plato::factory()->count(16)->for($this->soda)->create();

        $this->getJson("/api/v1/sodas/{$this->soda->id}/platos")
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16);
    }

    /** El detalle de un plato de la soda responde sus atributos. */
    #[Test]
    public function el_detalle_de_un_plato_responde_sus_atributos(): void
    {
        $plato = Plato::factory()->for($this->soda)->create(['nombre' => 'Casado', 'precio' => 2800]);

        $this->getJson("/api/v1/sodas/{$this->soda->id}/platos/{$plato->id}")
            ->assertOk()
            ->assertJsonPath('data.id', (string) $plato->id)
            ->assertJsonPath('data.attributes.nombre', 'Casado')
            ->assertJsonPath('data.attributes.precio', 2800);
    }

    /** Un plato de otra soda no existe en esta. */
    #[Test]
    public function un_plato_de_otra_soda_responde_404(): void
    {
        $ajeno = Plato::factory()->create();

        $this->getJson("/api/v1/sodas/{$this->soda->id}/platos/{$ajeno->id}")
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json');
    }

    /** Un plato que la soda sacó del menú no se muestra. */
    #[Test]
    public function un_plato_no_disponible_responde_404(): void
    {
        $plato = Plato::factory()->for($this->soda)->noDisponible()->create();

        $this->getJson("/api/v1/sodas/{$this->soda->id}/platos/{$plato->id}")->assertNotFound();
    }

    /** Una soda inexistente responde 404 sin revelar el nombre de la clase del modelo. */
    #[Test]
    public function una_soda_inexistente_responde_404_sin_revelar_clases(): void
    {
        $respuesta = $this->getJson('/api/v1/sodas/999999/platos')
            ->assertNotFound()
            ->assertJsonPath('detail', 'El recurso solicitado no existe.');

        $this->assertStringNotContainsString('Models', (string) $respuesta->getContent());
    }
}
