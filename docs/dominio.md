# Dominio de SodaYa

Actores, casos de uso core y lenguaje ubicuo. El detalle de cada requisito está en la [ERS](ers-ieee830.md).

## Actores

| Actor | Tipo | Meta | Cómo se identifica ante la API |
|---|---|---|---|
| Visitante | Principal | Ver qué hay de almuerzo y cuánto cuesta. | No se identifica: el menú es público. |
| Cliente | Principal | Pedir para llevar, pagar y saber cuándo está listo. | Token con la ability `pedidos`. |
| Cocina | Principal | Ver los pedidos de su soda y avanzarlos. | Token con `pedidos` y `cocina`. La soda sale del usuario. |
| Dueño | Principal | Administrar el menú y ver reportes. | Token con `pedidos`, `cocina` y `administrar`. |
| Agente de IA | Principal externo | Consultar sodas y menús en nombre de una persona. | Servidor MCP de solo lectura. |
| Stripe | De apoyo | Cobrar la tarjeta y avisar el resultado. | Firma HMAC del webhook. |
| Proveedor de IA, WhatsApp, Google Calendar | De apoyo | Leer la pizarra, avisar al cliente, agendar encargos. | SodaYa los llama con sus credenciales. |
| El reloj | Tiempo | Cancelar pedidos sin confirmar y reiniciar porciones. | Tareas programadas y colas. |

## Casos de uso core

Core son los casos sin los cuales no se puede vender un almuerzo.

| Caso de uso | Actor | Regla central | Errores que produce |
|---|---|---|---|
| Consultar el menú del día | Visitante | Solo platos disponibles de esa soda. | 404 |
| Ingresar | Cliente, personal | El token lleva las abilities del rol. | 422, 429 |
| Realizar un pedido | Cliente | El precio y el total los pone el servidor; nunca se vende de más. | 401, 404, 409, 422 |
| Ver mi pedido | Cliente | Un pedido ajeno no existe para quien consulta. | 401, 404 |
| Cancelar mi pedido | Cliente | Solo pendiente o confirmado; devuelve las porciones. | 401, 404, 409 |
| Pagar mi pedido | Cliente, Stripe | El monto sale del pedido guardado; no se cobra dos veces. | 401, 404, 409, 503 |
| Avanzar un pedido | Cocina | Pendiente, confirmado, listo, entregado, sin saltos. | 401, 403, 404, 409 |
| Administrar el menú | Dueño | Solo sobre los platos de su soda. | 401, 403, 404, 422 |

Las fichas completas de realizar, cancelar, avanzar y pagar un pedido están en el Apéndice B de la ERS.

## Lenguaje ubicuo

La misma palabra significa lo mismo con la clienta, en el contrato, en el código y en la base de datos.

| Término | Significado | Tabla | Código | Recurso |
|---|---|---|---|---|
| Soda | El negocio. Es el inquilino. | `sodas` | `Soda` | `/sodas/{soda}` |
| Plato | Elemento del menú con precio, tiempo de preparación y porciones. | `platos` | `Plato` | `/sodas/{soda}/platos` |
| Categoría | Agrupación de platos de una soda. | `categorias` | `Categoria` | `/cocina/categorias` |
| Horario | Franja de atención de un día de la semana. | `horarios` | `Horario` | `/cocina/horarios` |
| Porción | Unidad disponible de un plato en el día. Nunca es negativa. | `platos.porciones_disponibles` | `porciones_disponibles` | Atributo del plato |
| Pedido | Lo que un cliente pide para llevar en una soda. | `pedidos` | `Pedido` | `/pedidos/{pedido}` |
| Línea del pedido | Un plato del pedido con su precio congelado. | `lineas_pedido` | `LineaPedido` | Parte del pedido |
| Precio congelado | El precio que se cobró. No cambia si el plato cambia. | `lineas_pedido.precio_unitario` | `precio_unitario` | Atributo de la línea |
| Estado del pedido | Pendiente, confirmado, listo, entregado o cancelado. | `pedidos.estado` | `EstadoPedido` | Atributo `estado` |
| Pago | El cobro de un pedido. Un pedido puede existir sin pago. | `pagos` | `Pago` | `/pedidos/{pedido}/pago` |
| Personal | Quien trabaja en una soda: cocina o dueño. | `users` con `rol` y `soda_id` | `User` | `/tokens` |
| Cliente | Quien pide. No pertenece a ninguna soda. | `users` con rol `cliente` | `User` | `/tokens` |
| Colones | Moneda. Siempre enteros. | Columnas `integer` | `int` | `"moneda": "CRC"` |

### Estados del pedido

```text
pendiente ──► confirmado ──► listo ──► entregado
    │              │
    └──────┬───────┘
           ▼
       cancelado
```
