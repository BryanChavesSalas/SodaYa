<?php

namespace App\Models;

use Database\Factories\PlatoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nombre', 'precio', 'minutos_preparacion', 'porciones_disponibles', 'disponible'])]
class Plato extends Model
{
    /** @use HasFactory<PlatoFactory> */
    use HasFactory;

    /**
     * Tipos de los atributos: el dinero y las porciones son enteros.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'minutos_preparacion' => 'integer',
            'porciones_disponibles' => 'integer',
            'disponible' => 'boolean',
        ];
    }

    /**
     * Soda a la que pertenece el plato.
     *
     * @return BelongsTo<Soda, $this>
     */
    public function soda(): BelongsTo
    {
        return $this->belongsTo(Soda::class);
    }
}
