<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PlatoResource;
use App\Models\Plato;
use App\Models\Soda;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PlatoController
{
    /** Menú del día de una soda: sus platos disponibles, paginados. */
    public function index(Soda $soda): AnonymousResourceCollection
    {
        return PlatoResource::collection(
            $soda->platos()->where('disponible', true)->orderBy('nombre')->paginate(15),
        );
    }

    /** Detalle de un plato disponible del menú de la soda. */
    public function show(Soda $soda, Plato $plato): PlatoResource
    {
        abort_unless($plato->disponible, 404);

        return new PlatoResource($plato);
    }
}
