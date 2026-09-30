<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

final class RealizarPedidoRequest extends FormRequest
{
    /** La autorización la deciden la ruta y el token, no este Form Request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de forma del pedido: solo platos y cantidades, nunca precios.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'lineas' => ['required', 'array', 'min:1', 'max:20'],
            'lineas.*.plato_id' => ['required', 'integer:strict', 'distinct'],
            'lineas.*.cantidad' => ['required', 'integer:strict', 'min:1', 'max:20'],
        ];
    }
}
