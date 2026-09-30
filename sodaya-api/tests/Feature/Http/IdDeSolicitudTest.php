<?php

namespace Tests\Feature\Http;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IdDeSolicitudTest extends TestCase
{
    /** Toda respuesta lleva un UUID en el encabezado X-Request-Id. */
    #[Test]
    public function toda_respuesta_lleva_su_identificador(): void
    {
        $id = $this->get('/up')->headers->get('X-Request-Id');

        $this->assertTrue(Str::isUuid($id));
    }

    /** Se conserva el UUID que envía el cliente para rastrear la solicitud. */
    #[Test]
    public function se_conserva_el_uuid_del_cliente(): void
    {
        $id = (string) Str::uuid7();

        $this->get('/up', ['X-Request-Id' => $id])->assertHeader('X-Request-Id', $id);
    }

    /** Un identificador que no es UUID se reemplaza para no escribir texto libre en los logs. */
    #[Test]
    public function se_reemplaza_un_identificador_que_no_es_uuid(): void
    {
        $id = $this->get('/up', ['X-Request-Id' => 'falso registro'])->headers->get('X-Request-Id');

        $this->assertNotSame('falso registro', $id);
        $this->assertTrue(Str::isUuid($id));
    }
}
