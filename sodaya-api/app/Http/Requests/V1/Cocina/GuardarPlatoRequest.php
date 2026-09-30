<?php

namespace App\Http\Requests\V1\Cocina;

use Illuminate\Foundation\Http\FormRequest;

final class GuardarPlatoRequest extends FormRequest
{
    /** La autorización la deciden la ruta y el token, no este Form Request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de forma de un plato nuevo.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return self::reglas();
    }

    /**
     * Reglas de forma que comparten crear y editar un plato.
     *
     * @return array<string, list<string>>
     */
    public static function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'precio' => ['required', 'integer:strict', 'min:100', 'max:100000'],
            'minutos_preparacion' => ['required', 'integer:strict', 'min:1', 'max:120'],
            'porciones_disponibles' => ['required', 'integer:strict', 'min:0', 'max:500'],
            'disponible' => ['sometimes', 'boolean:strict'],
        ];
    }
}
