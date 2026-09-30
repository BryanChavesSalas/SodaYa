<?php

namespace App\Models;

use Database\Factories\SodaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'telefono'])]
class Soda extends Model
{
    /** @use HasFactory<SodaFactory> */
    use HasFactory;

    /**
     * Platos del menú de la soda.
     *
     * @return HasMany<Plato, $this>
     */
    public function platos(): HasMany
    {
        return $this->hasMany(Plato::class);
    }
}
