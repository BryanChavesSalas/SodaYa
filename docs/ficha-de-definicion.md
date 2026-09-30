# Ficha de definición: SodaYa

| Campo | Definición |
|---|---|
| Producto | SodaYa: menú del día y pedidos para llevar, cobrados por adelantado. |
| Negocio | Sodas de Costa Rica. Cada soda es un inquilino con sus datos separados. |
| Clienta | Lucía Araya Quesada, Soda La Esquina (persona ficticia). |
| Piezas | `sodaya-api` (Laravel 13 y PostgreSQL 18) y `sodaya-web` (Vue 3 con TypeScript). |
| Requisitos | [ERS según IEEE 830](ers-ieee830.md). |

## Roles

| Rol | Qué hace | Ability del token |
|---|---|---|
| Visitante | Consulta el menú sin cuenta. | Ninguna. |
| Cliente | Pide, paga, cancela y deja reseñas de sus pedidos. | `pedidos` |
| Cocina | Ve los pedidos de su soda y los avanza. | `pedidos`, `cocina` |
| Dueño | Administra menú, horario, personal y promociones, y ve reportes. | `pedidos`, `cocina`, `administrar` |

## Extras

| Extra | Qué agrega | Clase |
|---|---|---|
| Hora feliz | Descuento automático en la franja de poca venta. | 12 |
| Propinas | Propina validada en el servidor e incluida en el cobro. | 14 |
| Comisiones | Reparto de cada cobro entre la soda y la plataforma. | 14 |
| Google Calendar | Encargos con fecha sincronizados al calendario del dueño. | 16 |
| WhatsApp | Aviso de pedido listo por WhatsApp Business Cloud. | 19 |
| Reseñas con IA | Resumen de fortalezas, quejas y tono de las reseñas. | 20 |
| Servidor MCP | Consulta de sodas y menús desde un asistente de IA. | 22 |

## Lo que SodaYa no hará

- Procesar dinero real ni ofrecerse como servicio comercial durante el curso.
- Guardar datos de personas reales.
- Integrar ONVO Pay, PayPal, Tilopay, GreenPay ni SINPE Móvil: son parte de la Investigación 1.
- Cobrar con dos pasarelas intercambiables: ese patrón se enseña con los canales de aviso.
- Emitir factura electrónica.
- Hacer entregas a domicilio, reservas de mesa ni control de inventario de ingredientes.
- Manejar agendas por franja horaria ni ninguna de las verticales del proyecto integrador.
