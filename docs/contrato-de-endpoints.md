# Contrato de endpoints

Diseñado antes de programar, a partir de los casos de uso del [dominio](dominio.md). Todas las rutas viven bajo `/api/v1`. El contrato de diseño en OpenAPI 3.1 está en [`contrato/openapi.yaml`](contrato/openapi.yaml); el vigente lo genera Scramble desde el código y se versiona en `sodaya-api/openapi/v1.json`.

| Método | Ruta | Quién | Respuestas | Clase |
|---|---|---|---|---|
| `GET` | `/sodas/{soda}/platos` | Visitante | 200, 404 | 5 |
| `GET` | `/sodas/{soda}/platos/{plato}` | Visitante | 200, 404 | 5 |
| `POST` | `/sodas/{soda}/pedidos` | Cliente | 201, 401, 404, 409, 422 | 7 |
| `POST` | `/tokens` | Cliente, personal | 201, 422, 429 | 8 |
| `DELETE` | `/tokens/actual` | Autenticado | 204, 401 | 8 |
| `GET` | `/cocina/platos` | Cocina, dueño | 200, 401, 403 | 8 |
| `POST` | `/cocina/platos` | Dueño | 201, 401, 403, 422 | 8 |
| `PATCH` | `/cocina/platos/{plato}` | Dueño | 200, 401, 403, 404, 422 | 8 |
| `GET` | `/pedidos/{pedido}` | Cliente dueño del pedido, personal de la soda | 200, 401, 404 | 9 |
| `POST` | `/pedidos/{pedido}/cancelacion` | Cliente dueño del pedido | 200, 401, 404, 409 | 9 |
| `GET` | `/cocina/pedidos` | Cocina, dueño | 200, 401, 403 | 9 |
| `PATCH` | `/cocina/pedidos/{pedido}/estado` | Cocina, dueño | 200, 401, 403, 404, 409 | 9 |
| `POST` | `/pedidos/{pedido}/pago` | Cliente dueño del pedido | 201, 401, 404, 409, 503 | 11 |

## Reglas de diseño

- **Recursos con el vocabulario del negocio**, en español y en plural: `/sodas`, `/platos`, `/pedidos`.
- **Anidar por dueño** cuando un recurso depende de otro: un plato es de una soda, `/sodas/{soda}/platos`.
- **Una acción que no es CRUD se vuelve un recurso**: cancelar un pedido crea una cancelación, `POST /pedidos/{pedido}/cancelacion`.
- **La soda del personal no viaja en la URL**: `/cocina/...` la toma del usuario autenticado.
- **Atributos en `snake_case`** y dinero en colones enteros: `precio_unitario: 2800`.
- **Cada operación tiene un `operationId` estable**, igual al nombre de su ruta en Laravel (`v1.sodas.pedidos.store`).

## Errores

Todo error responde `application/problem+json` (RFC 9457). El catálogo cerrado de tipos está en la sección 3.5.1 de la [ERS](ers-ieee830.md).

- **422** cuando la solicitud está mal formada y el cliente la corrige cambiando los datos.
- **409** cuando está bien formada pero choca con el estado actual del negocio.
- **404** también cuando el recurso existe pero es de otra soda o de otra persona: no se revela que existe.
