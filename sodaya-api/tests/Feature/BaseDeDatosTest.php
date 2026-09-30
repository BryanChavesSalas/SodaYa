<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BaseDeDatosTest extends TestCase
{
    /** Las pruebas corren en PostgreSQL 18, el mismo motor de producción. */
    #[Test]
    public function las_pruebas_corren_en_postgresql_18(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->assertSame('sodaya_test', DB::connection()->getDatabaseName());
        $this->assertStringStartsWith('18.', (string) DB::scalar('show server_version'));
    }
}
