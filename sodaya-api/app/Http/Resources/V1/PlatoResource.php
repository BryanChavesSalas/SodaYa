<?php

namespace App\Http\Resources\V1;

use App\Models\Plato;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin Plato */
final class PlatoResource extends JsonApiResource
{
    /**
     * Atributos públicos del plato; nunca la soda, las fechas ni campos internos.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'moneda' => 'CRC',
            'minutos_preparacion' => $this->minutos_preparacion,
            'porciones_disponibles' => $this->porciones_disponibles,
        ];
    }

    /**
     * Enlace al detalle del plato dentro de su soda.
     *
     * @return array<string, string>
     */
    public function toLinks(Request $request): array
    {
        return ['self' => route('v1.sodas.platos.show', [$this->soda_id, $this->id])];
    }
}
