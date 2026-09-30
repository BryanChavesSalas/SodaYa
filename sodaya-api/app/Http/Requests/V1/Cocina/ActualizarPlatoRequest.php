<?php

namespace App\Http\Requests\V1\Cocina;

use Illuminate\Foundation\Http\FormRequest;

final class ActualizarPlatoRequest extends FormRequest
{
    /** La autorización la deciden la ruta y el token, no este Form Request. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas del plato con cada campo opcional, porque un PATCH envía solo lo que cambia.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return array_map(
            fn (array $reglas): array => ['sometimes', ...array_values(array_diff($reglas, ['required', 'sometimes']))],
            GuardarPlatoRequest::reglas(),
        );
    }
}
