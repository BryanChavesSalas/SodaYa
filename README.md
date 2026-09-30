# SodaYa

Proyecto guía del curso ISW-621 (UTN): plataforma multi-inquilino donde varias sodas publican su menú del día y reciben pedidos para llevar. API REST en Laravel 13 y PostgreSQL, cliente SPA en Vue 3 con TypeScript, cobro con Stripe, cocina en tiempo real, IA con datos validados, CI/CD y despliegue en producción.

## Estructura

| Carpeta | Contenido |
|---|---|
| [`sodaya-api/`](sodaya-api/README.md) | API REST en Laravel 13 sobre PostgreSQL 18. Instalación y pruebas en su README. |

## Uso de asistentes de IA

**Qué se genera con asistencia de IA.** El andamiaje, el código, las pruebas y la documentación pueden generarse con un asistente de IA. Las reglas que debe seguir están en [`sodaya-api/AGENTS.md`](sodaya-api/AGENTS.md).

**Cómo se verifica.** Lo generado pasa por las mismas verificaciones que el código escrito a mano:

- Pruebas de PHPUnit que cubren el cambio, corriendo contra PostgreSQL.
- Formato con Pint y análisis estático con Larastan.
- Revisión del pull request antes de integrarlo a `main`.

## Recursos

La API se diseña alrededor de los recursos del negocio, con el vocabulario de la clienta: en español, en plural y en `snake_case`.

| Recurso | Qué representa | Rutas principales |
|---|---|---|
| `sodas` | El negocio. Es el inquilino: casi todo lo demás le pertenece. | `/api/v1/sodas/{soda}` |
| `platos` | Lo que vende una soda, con precio, tiempo de preparación y porciones del día. | `/api/v1/sodas/{soda}/platos` |
| `categorias` | La agrupación de los platos de una soda: desayunos, casados, bebidas. | `/api/v1/cocina/categorias` |
| `horarios` | Los días y las horas en que una soda atiende. | `/api/v1/cocina/horarios` |
| `pedidos` | Lo que un cliente pide para llevar, con sus líneas y su estado. | `/api/v1/sodas/{soda}/pedidos`, `/api/v1/pedidos/{pedido}` |
| Personas | Clientes, que piden, y personal (cocina y dueño), que atiende una soda. | `/api/v1/tokens` |

## Ciclo HTTP de pedir un casado

Un cliente pide dos casados y un gallo pinto en la soda 1.

**Solicitud**

```http
POST /api/v1/sodas/1/pedidos HTTP/1.1
Host: api.sodaya.test
Accept: application/json
Content-Type: application/json
Authorization: Bearer 1|q8FzT0xk3Yb9...

{
  "lineas": [
    { "plato_id": 3, "cantidad": 2 },
    { "plato_id": 5, "cantidad": 1 }
  ]
}
```

**Respuesta**

```http
HTTP/1.1 201 Created
Content-Type: application/vnd.api+json
Location: https://api.sodaya.test/api/v1/pedidos/01a0d57b-94c3-7344-bb39-1c859fd361c8
X-Request-Id: 01a0d57b-94c5-70d1-9c1e-4be2f1b0a7d3

{
  "data": {
    "id": "01a0d57b-94c3-7344-bb39-1c859fd361c8",
    "type": "pedidos",
    "attributes": {
      "estado": "pendiente",
      "total": 7400,
      "moneda": "CRC"
    }
  }
}
```

| Parte | Valor | Por qué |
|---|---|---|
| Método | `POST` | Crea un recurso nuevo. No es idempotente: repetirlo crea otro pedido. |
| URL | `/api/v1/sodas/1/pedidos` | El pedido se hace en una soda. `v1` es la versión del contrato. |
| `Accept` | `application/json` | El cliente espera JSON, también cuando hay un error. |
| `Content-Type` | `application/json` | El cuerpo viaja en JSON. |
| `Authorization` | `Bearer <token>` | Identifica al cliente. Sin token, la respuesta es 401. |
| Cuerpo | `plato_id` y `cantidad` | El navegador nunca envía precios: el servidor congela el precio vigente y calcula el total. |
| Código | `201 Created` | El recurso se creó. `Location` dice dónde consultarlo. |
| `X-Request-Id` | UUID | Identifica la solicitud en los logs y en los errores. |

Si algo falla, la respuesta usa `application/problem+json` (RFC 9457):

| Código | Cuándo |
|---|---|
| `401` | Falta el token o venció. |
| `404` | La soda no existe. |
| `409` | La soda está cerrada o se agotaron las porciones. |
| `422` | El pedido no trae líneas o una cantidad está fuera de 1 a 20. |
