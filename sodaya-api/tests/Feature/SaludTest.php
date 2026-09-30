<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SaludTest extends TestCase
{
    /** La ruta de salud responde mientras la aplicación arranca bien. */
    #[Test]
    public function la_ruta_de_salud_responde(): void
    {
        $this->get('/up')->assertOk();
    }
}
