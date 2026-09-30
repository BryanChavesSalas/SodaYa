# Normalización del esquema

El esquema de SodaYa está en tercera forma normal. Se parte de cómo la soda anota hoy los pedidos, en una sola planilla:

| Pedido | Fecha | Cliente | Tel. cliente | Soda | Tel. soda | Platos pedidos |
|---|---|---|---|---|---|---|
| P-1 | 28/09 7:10 | Ana Mora | 8888-1111 | Soda La Esquina | 2460-1122 | Gallo pinto x2 a ₡2200 (Desayunos); Café chorreado x2 a ₡800 (Bebidas) |
| P-2 | 28/09 12:05 | Ana Mora | 8888-1111 | Soda La Esquina | 2460-1122 | Casado de pollo x1 a ₡3200 (Casados) |

## Primera forma normal

Cada celda guarda un solo valor y no hay grupos repetidos. "Platos pedidos" es una lista: se separa una fila por cada plato de cada pedido.

| Pedido | Plato | Categoría | Precio | Cantidad | Fecha | Cliente | Tel. cliente | Soda | Tel. soda |
|---|---|---|---|---|---|---|---|---|---|
| P-1 | Gallo pinto | Desayunos | 2200 | 2 | 28/09 7:10 | Ana Mora | 8888-1111 | Soda La Esquina | 2460-1122 |
| P-1 | Café chorreado | Bebidas | 800 | 2 | 28/09 7:10 | Ana Mora | 8888-1111 | Soda La Esquina | 2460-1122 |

## Segunda forma normal

Todo atributo depende de la llave completa, (pedido, plato), y no de una parte:

- La fecha, el cliente y la soda dependen solo del pedido: van a `pedidos`.
- La categoría depende solo del plato: va a `platos`.
- En `lineas_pedido` queda lo que depende de los dos juntos: la cantidad y el precio al que se vendió.

**El precio congelado no rompe la 2FN.** El precio actual depende solo del plato y vive en `platos.precio`. El de la línea es el que se cobró en ese pedido: depende del pedido y del plato juntos. Por la misma razón la línea guarda `nombre_plato`.

**`lineas_pedido` no lleva `soda_id`.** La soda depende solo del pedido, que es parte de la llave: sería una dependencia parcial.

## Tercera forma normal

Ningún atributo depende de otro que no sea llave:

| Dependencia transitiva | Qué pasaría si se queda | Solución |
|---|---|---|
| pedido → cliente → teléfono del cliente | Si el cliente cambia de número, hay que corregir todos sus pedidos. | El teléfono va en `users`; el pedido guarda `cliente_id`. |
| pedido → soda → teléfono de la soda | Lo mismo con la soda. | Tabla `sodas`; el pedido guarda `soda_id`. |
| plato → categoría como texto | "Bebidas" escrito diez veces; un error de dedo crea otra categoría. | Tabla `categorias` y `platos.categoria_id` (clase 7). |

**El total no se guarda.** Es la suma de cantidad por precio de las líneas. Guardarlo daría dos fuentes para el mismo dato, que pueden contradecirse.

**El horario va en filas**, una por día, en la tabla `horarios` (clase 7), y no en siete columnas de `sodas`.

## Esquema resultante

| Tabla | Columnas | Restricciones | Clase |
|---|---|---|---|
| `sodas` | id, nombre, telefono | | 4 |
| `platos` | id, soda_id, nombre, precio, minutos_preparacion, porciones_disponibles, disponible | único (soda_id, nombre); precio de 100 a 100 000; minutos de 1 a 120; porciones ≥ 0 | 4 |
| `pedidos` | id (UUID v7), soda_id, cliente_id, estado, realizado_en | estado dentro de los cinco valores | 4 |
| `lineas_pedido` | id, pedido_id, plato_id, nombre_plato, precio_unitario, cantidad | único (pedido_id, plato_id); precio > 0; cantidad de 1 a 20 | 4 |
| `categorias` | id, soda_id, nombre | único (soda_id, nombre) | 7 |
| `horarios` | id, soda_id, dia_semana, abre, cierra | único (soda_id, dia_semana); abre < cierra | 7 |

Las restricciones `CHECK` son la última defensa: garantizan la integridad aunque el código falle. PostgreSQL no tiene enteros sin signo, así que los rangos van como `CHECK`.
