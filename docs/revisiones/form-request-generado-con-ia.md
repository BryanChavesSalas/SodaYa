# Revisión de un Form Request generado con IA

Clase 4 · issue #8. Se le pidió a un asistente de IA el Form Request para crear un plato, y se revisó contra la ERS y contra las restricciones de la base de datos.

## Instrucción

> Genera el Form Request de Laravel 13 para crear un plato de una soda: nombre, precio, minutos de preparación y porciones disponibles.

## Borrador del asistente

```php
public function authorize(): bool
{
    return false;
}

public function rules(): array
{
    return [
        'nombre' => 'required|string',
        'precio' => 'required|numeric|min:0',
        'minutos_preparacion' => 'required|integer',
        'porciones_disponibles' => 'required|integer',
        'soda_id' => 'required|exists:sodas,id',
    ];
}
```

## Qué dejó sin validar

| Hallazgo | Qué pasaría | Corrección en `GuardarPlatoRequest` |
|---|---|---|
| `authorize()` devuelve `false`, el valor que deja `make:request`. | Toda solicitud responde 403. | Devuelve `true`: autorizar le toca a la ruta y al token. |
| `nombre` sin largo máximo. | Un nombre de 500 caracteres choca con `varchar(120)` y responde 500. | `max:120`. |
| `precio` con `numeric`. | Acepta ₡2800,50 y el texto `"2800"`. | `integer:strict`: colones enteros enviados como número. |
| `precio` con `min:0`. | Acepta un plato de ₡0. | De ₡100 a ₡100 000, como pide RF-MEN-02. |
| `minutos_preparacion` sin rango. | 0 o 999 minutos llegan a la base, el `CHECK` los rechaza y la API responde 500. | De 1 a 120. |
| `porciones_disponibles` sin rango. | Porciones negativas terminan en otro 500. | De 0 a 500. |
| `soda_id` en el cuerpo. | El personal de una soda crea platos en otra: asignación masiva. | Se quita. La soda sale del usuario autenticado (clase 8). |
| `exists:sodas,id` sin alcance. | Revela qué sodas existen. | Se quita junto con `soda_id`. |

## Conclusión

El borrador funcionaba en el caso feliz y fallaba en todos los bordes. La revisión compara cada campo con la ERS y con los `CHECK` de la base, y pregunta de dónde sale cada dato. Las reglas corregidas están cubiertas por `tests/Feature/Http/Requests/ReglasDePlatoTest.php`.
