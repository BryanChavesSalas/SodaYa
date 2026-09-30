<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ContratoOpenApiTest extends TestCase
{
    /** El contrato versionado coincide con el que genera el código. */
    #[Test]
    public function el_contrato_versionado_esta_al_dia(): void
    {
        $generado = tempnam(sys_get_temp_dir(), 'openapi').'.json';

        Artisan::call('scramble:export', ['--path' => $generado]);

        $this->assertJsonFileEqualsJsonFile(
            base_path('openapi/v1.json'),
            $generado,
            'El contrato cambió. Ejecute: php artisan scramble:export --path=openapi/v1.json',
        );
    }

    /** Los errores del contrato se documentan como problem details y no con el formato de Laravel. */
    #[Test]
    public function los_errores_se_documentan_como_problem_details(): void
    {
        $contrato = (string) file_get_contents(base_path('openapi/v1.json'));

        $this->assertStringContainsString('application/problem+json', $contrato);
        $this->assertStringNotContainsString('"message"', $contrato);
    }
}
