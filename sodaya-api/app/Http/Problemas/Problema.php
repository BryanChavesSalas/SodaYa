<?php

namespace App\Http\Problemas;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Context;

final readonly class Problema
{
    /**
     * Arma un problem details de RFC 9457 sin trazas, SQL ni rutas internas.
     *
     * @param  array<string, mixed>  $extensiones
     * @param  array<string, mixed>  $encabezados
     */
    public function __construct(
        public int $estado,
        public string $tipo,
        public string $titulo,
        public string $detalle,
        public array $extensiones = [],
        public array $encabezados = [],
    ) {}

    /**
     * Crea un problema de un tipo del catálogo con el detalle de esta ocurrencia.
     *
     * @param  array<string, mixed>  $extensiones
     * @param  array<string, mixed>  $encabezados
     */
    public static function de(TipoDeProblema $tipo, ?string $detalle = null, array $extensiones = [], array $encabezados = []): self
    {
        return new self($tipo->estado(), $tipo->uri(), $tipo->titulo(), $detalle ?? $tipo->detalle(), $extensiones, $encabezados);
    }

    /** Responde application/problem+json con el identificador de la solicitud en instance. */
    public function aRespuesta(): JsonResponse
    {
        $id = Context::get('request_id');

        return new JsonResponse(
            data: [
                'type' => $this->tipo,
                'title' => $this->titulo,
                'status' => $this->estado,
                'detail' => $this->detalle,
                'instance' => is_string($id) ? "urn:uuid:{$id}" : null,
                ...$this->extensiones,
            ],
            status: $this->estado,
            headers: [...$this->encabezados, 'Content-Type' => 'application/problem+json'],
            options: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
