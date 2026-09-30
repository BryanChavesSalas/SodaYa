# SodaYa API: instrucciones para asistentes de IA

## Stack

- Laravel 13 en modo API, PHP 8.4 o superior.
- PostgreSQL 18.6 en todos los ambientes, también en pruebas y CI. Nunca SQLite.
- PHPUnit 12, Sanctum con tokens y Scramble para el contrato OpenAPI.

## Comandos

| Para | Comando |
|---|---|
| Pruebas (base `sodaya_test`) | `php artisan test` |
| Formato | `vendor/bin/pint` |
| Contrato OpenAPI, después de cambiar la API | `php artisan scramble:export --path=openapi/v1.json` |

## Reglas del proyecto

- El dinero se guarda en colones enteros (`integer`). Nunca `float` ni `decimal`.
- El vocabulario es el de la ERS (`docs/ers-ieee830.md`): recursos en español y en plural, atributos en `snake_case`.
- La soda del personal sale siempre del usuario autenticado, nunca de la URL, los parámetros ni el cuerpo.
- La entrada se valida con Form Requests y la salida se arma con API Resources, que funcionan como lista blanca.
- Todo error responde `application/problem+json` (RFC 9457).
- Los proveedores de pago y mensajería se consumen por HTTP directo con el cliente `Http` de Laravel, con `timeout`, sin SDK.
- Las fechas se guardan en UTC; las reglas de horario usan `America/Costa_Rica`.
- Toda técnica nueva llega con su prueba de PHPUnit.

## Estilo

- Un comentario corto y preciso antes de cada método o función. Ningún otro comentario.
- Controladores `final`, sin heredar de `Controller`.
- Commits atómicos con Conventional Commits: tipo en inglés y descripción en español, en imperativo.
- Ningún commit ni pull request lleva firmas, trailers ni marcas de atribución.
