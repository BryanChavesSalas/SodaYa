# Especificación de Requisitos de Software: SodaYa

**Plataforma multi-inquilino de menú del día y pedidos para llevar para sodas**

Elaborada según IEEE Std 830-1998, *Recommended Practice for Software Requirements Specifications*.

| Campo | Valor |
|---|---|
| Proyecto | SodaYa, proyecto guía del curso ISW-621 Programación en Ambiente Web II |
| Institución | Universidad Técnica Nacional, sede San Carlos |
| Versión | 1.0 |
| Fecha | 29 de setiembre de 2026 |
| Estado | Aprobada para desarrollo |
| Elaborada por | Ing. Bryan Miguel Chaves Salas, docente y líder técnico |
| Clienta | Lucía Araya Quesada, dueña de Soda La Esquina (persona ficticia) |

> **Nota didáctica.** El levantamiento de requisitos es una simulación: la clienta, su soda y las citas de la entrevista son ficticias. El documento sirve como modelo para que cada equipo elabore la especificación de su propia vertical del proyecto integrador. Las decisiones técnicas sí son las que se construyen en clase.

## Control de versiones

| Versión | Fecha | Autor | Cambios |
|---|---|---|---|
| 0.1 | 25/09/2026 | B. Chaves | Acta de la entrevista y observación en la soda (Apéndice A). |
| 0.9 | 28/09/2026 | B. Chaves | Borrador completo revisado con la clienta. |
| 1.0 | 29/09/2026 | B. Chaves | Versión aprobada. Línea base para el desarrollo del curso. |

## Aprobaciones

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Clienta | Lucía Araya Quesada | | |
| Líder técnico | Bryan Miguel Chaves Salas | | |

## Contenido

1. Introducción
2. Descripción general
3. Requisitos específicos
4. Apéndices: A. Acta de levantamiento · B. Casos de uso core · C. Matriz de trazabilidad · D. Asuntos pendientes

---

## 1. Introducción

### 1.1 Propósito

Este documento especifica los requisitos funcionales y no funcionales de SodaYa, una plataforma web donde varias sodas publican su menú del día y reciben pedidos para llevar, cobrados por adelantado.

Está dirigido a:

- **La clienta**, para confirmar que el sistema descrito resuelve los problemas de su negocio.
- **El equipo de desarrollo**, como línea base de lo que se construye, se prueba y se entrega en cada clase.
- **Las personas estudiantes del curso**, como modelo de especificación para su proyecto integrador.
- **Quien audite el sistema**, como referencia de lo que el sistema promete cumplir.

### 1.2 Alcance

**Nombre del producto:** SodaYa. Consta de dos piezas:

- `sodaya-api`: API REST en Laravel 13 sobre PostgreSQL.
- `sodaya-web`: cliente SPA en Vue 3 con TypeScript, que consume únicamente la API.

**Qué hace SodaYa:**

- Publica el menú del día de cada soda, con precios, porciones disponibles y horario.
- Recibe pedidos para llevar sin vender porciones que ya no existen, ni siquiera cuando dos clientes piden la última al mismo tiempo.
- Cobra el pedido por adelantado con una pasarela de pago en modo de prueba.
- Permite a la cocina ver y avanzar los pedidos de su soda en tiempo real.
- Mantiene separados los datos de cada soda, y los de cada cliente.
- Premia a los clientes frecuentes con puntos, ofrece lista de espera para platos agotados y automatiza avisos y cancelaciones.
- Convierte con inteligencia artificial la pizarra del menú en platos validados.

**Qué no hace SodaYa** (acordado con la clienta):

- No procesa dinero real ni se ofrece como servicio comercial mientras dure el curso.
- No guarda datos de personas reales.
- No integra ONVO Pay, PayPal, Tilopay, GreenPay ni SINPE Móvil. Son parte de la Investigación 1 de los equipos.
- No implementa dos pasarelas de pago intercambiables. El patrón de proveedores intercambiables se enseña con los canales de aviso.
- No emite factura electrónica del Ministerio de Hacienda.
- No hace entregas a domicilio, reservas de mesa ni control de inventario de ingredientes.
- No maneja agendas por franja horaria ni ninguna de las verticales del proyecto integrador.

**Beneficios esperados para la soda:**

- Menos llamadas y mensajes preguntando qué hay de almuerzo.
- Cero porciones vendidas de más.
- Menos comida perdida por pedidos que nadie recoge, gracias al cobro por adelantado.
- Una cocina ordenada, sin papelitos.
- Información para decidir qué cocinar y a qué hora ofrecer descuentos.

### 1.3 Definiciones, acrónimos y abreviaturas

**Términos del negocio (lenguaje ubicuo).** La misma palabra significa lo mismo en la conversación con la clienta, en el contrato de la API, en el código y en la base de datos.

| Término | Significado |
|---|---|
| Soda | Negocio de comida típica costarricense que vende un menú del día. Es el **inquilino** del sistema. |
| Plato | Elemento del menú de una soda, con precio, categoría, tiempo de preparación y porciones del día. |
| Categoría | Agrupación de platos dentro de una soda, por ejemplo Desayunos, Casados o Bebidas. |
| Porción | Unidad disponible de un plato en el día. Se agota y nunca es negativa. |
| Horario | Días y horas en que una soda atiende. Se compone de franjas horarias por día de la semana. |
| Pedido | Lo que un cliente pide para llevar en una soda. Contiene una o más líneas. |
| Línea del pedido | Un plato del pedido, con la cantidad y el precio **congelado** del momento en que se pidió. |
| Precio congelado | El precio que se cobró en un pedido. No cambia aunque después cambie el precio del plato. |
| Estado del pedido | Pendiente, confirmado, listo, entregado o cancelado. |
| Pago | El cobro de un pedido a través de la pasarela. Un pedido puede existir sin pago. |
| Personal | Persona que trabaja en una soda: cocina o dueño. |
| Cliente | Persona que pide. No pertenece a ninguna soda. |
| Visitante | Persona que consulta el menú sin haber ingresado. |
| Puntos | Saldo de lealtad del cliente, calculado como la suma de sus movimientos. |
| Hora feliz | Promoción de precio para toda una soda en un día de la semana y una franja horaria. |
| Lista de espera | Fila de clientes interesados en un plato agotado. |
| Pizarra | Menú escrito a mano que la soda cuelga cada mañana. |
| Colones (₡) | Moneda de Costa Rica. En SodaYa se manejan siempre como números enteros. |

**Términos técnicos:**

| Término | Significado |
|---|---|
| API REST | Interfaz de programación sobre HTTP con recursos, métodos y códigos de estado. |
| SPA | Aplicación de una sola página que se ejecuta en el navegador. |
| Multi-inquilino | Varias sodas comparten el sistema y la base de datos con sus datos completamente separados. |
| Token | Credencial que la API entrega al ingresar y que se envía en cada petición. |
| Ability | Permiso que lleva un token. Un token solo puede lo que dice su lista de abilities. |
| Webhook | Notificación HTTP que un proveedor envía a SodaYa cuando algo cambia, por ejemplo un pago aprobado. |
| HMAC | Firma calculada con una clave secreta compartida que permite verificar el origen de un mensaje. |
| Idempotente | Operación que produce el mismo resultado aunque se ejecute más de una vez. |
| Cola | Mecanismo para ejecutar trabajos en segundo plano, sin hacer esperar a quien hizo la petición. |
| p95 | Percentil 95: el 95 % de las peticiones responde en ese tiempo o menos. |
| N+1 | Defecto de rendimiento en que un listado ejecuta una consulta adicional por cada elemento. |
| MCP | Model Context Protocol. Estándar para que un asistente de IA use herramientas externas. |
| SSE | Server-Sent Events. El servidor envía eventos al navegador por una conexión abierta. |

**Acrónimos:**

| Acrónimo | Significado |
|---|---|
| ERS | Especificación de Requisitos de Software |
| RF / RNF | Requisito funcional / Requisito no funcional |
| CI/CD | Integración continua / Despliegue continuo |
| OWASP | Open Worldwide Application Security Project |
| PRODHAB | Agencia de Protección de Datos de los Habitantes |
| RPO / RTO | Pérdida máxima de datos tolerada / Tiempo máximo de recuperación |
| UTN | Universidad Técnica Nacional |

### 1.4 Referencias

| Referencia | Uso en este documento |
|---|---|
| IEEE Std 830-1998, *Recommended Practice for Software Requirements Specifications*. | Estructura del documento. |
| ISO/IEC/IEEE 29148:2018, *Requirements engineering*. | Norma que actualiza a IEEE 830; criterios de calidad de cada requisito. |
| RFC 9457, *Problem Details for HTTP APIs* (IETF, 2023). | Formato único de errores. |
| RFC 9110, *HTTP Semantics* (IETF, 2022). | Métodos y códigos de estado. |
| OpenAPI Specification 3.1.0. | Contrato de la API. |
| OWASP API Security Top 10, edición 2023. | Requisitos de seguridad. |
| OWASP Top 10 for LLM Applications, edición 2025. | Requisitos de la funcionalidad de IA. |
| Ley 8968, Protección de la Persona frente al Tratamiento de sus Datos Personales, Costa Rica. | Requisitos de privacidad. |
| WCAG 2.2, W3C. | Requisitos de accesibilidad del cliente web. |
| Programa del curso ISW-621 y enunciado del proyecto integrador, III cuatrimestre 2026. | Restricciones académicas y calendario. |
| Plan de clases del proyecto guía SodaYa, versión del 24/09/2026. | Clase en que se implementa cada requisito. |
| Guía "Del dominio al contrato: actores, casos de uso, lenguaje ubicuo, OpenAPI y Swagger UI". | Actores, casos de uso y glosario. |

### 1.5 Visión general del documento

- La **sección 2** describe el producto en términos generales: su contexto, sus funciones, sus usuarios, sus restricciones y lo que se deja para después.
- La **sección 3** especifica cada requisito con un identificador único, su prioridad, su origen y la clase en que se construye.
- Los **apéndices** contienen el acta de la entrevista con la clienta, las fichas de los casos de uso core, la matriz de trazabilidad y los asuntos que quedan por confirmar.

**Cómo leer cada requisito:**

| Columna | Significado |
|---|---|
| ID | Identificador único y estable. Nunca se reutiliza, aunque el requisito se elimine. |
| Prioridad | **Esencial**: sin él, el producto no se puede vender. **Deseable**: diferencia el producto y se construye si el tiempo lo permite. **Opcional**: se documenta, pero no se compromete. |
| Origen | Necesidad de la clienta (N-xx, Apéndice A) o regla técnica (RT) que lo justifica. |
| Clase | Clase del curso en que se construye, según el plan de clases. |

Cada requisito se redacta de modo que se pueda verificar con una prueba automatizada, una prueba manual o una revisión.

---

## 2. Descripción general

### 2.1 Perspectiva del producto

SodaYa es un producto nuevo e independiente. Sustituye el proceso manual de la soda: pizarra en la puerta, cuaderno de pedidos, llamadas y mensajes de WhatsApp.

**Contexto del sistema:**

```
                      +--------------------------+
  Visitante --------> |    sodaya-web (Vue 3)    |
  Cliente ----------> |            |             |
  Cocina -----------> |            v             |
  Dueño ------------> |   sodaya-api (Laravel)   | <---> Stripe (pagos y webhooks)
                      |                          | <---> Proveedor de IA (Claude)
  Agente de IA -----> |   servidor MCP           | ----> WhatsApp Business Cloud
                      |            |             | ----> Google Calendar
  El reloj ---------> |            v             | ----> Correo (SMTP)
  (tareas programadas)|   PostgreSQL y Redis     |
                      +--------------------------+
```

#### 2.1.1 Interfaces del sistema

- El cliente web y cualquier otro consumidor se comunican con la API únicamente por HTTP con JSON, bajo `/api/v1`.
- Los proveedores externos se integran detrás de interfaces propias (puertos), de modo que cambiar de proveedor afecte a un solo componente.

#### 2.1.2 Interfaces de usuario

- Cliente web responsivo para visitantes, clientes, cocina y dueño.
- Documentación interactiva de la API con Swagger UI, para personas desarrolladoras.
- Tablero de monitoreo con Laravel Pulse, para quien opera el sistema.

#### 2.1.3 Interfaces de hardware

No se requiere hardware especial:

- Clientes: teléfono celular con navegador moderno.
- Cocina: tableta o computadora con navegador moderno.
- Dueño: celular con cámara para fotografiar la pizarra.

#### 2.1.4 Interfaces de software

Stripe en modo de prueba, el proveedor de IA, WhatsApp Business Cloud, Google Calendar y un servidor de correo. El detalle está en la sección 3.1.3.

#### 2.1.5 Interfaces de comunicaciones

HTTPS para todo el tráfico. WebSocket seguro para la pantalla de cocina y SSE para el estado del pedido.

#### 2.1.6 Restricciones de memoria

No hay restricciones especiales. El sistema debe operar en un servidor virtual de 2 GB de RAM (RNF-POR-04).

#### 2.1.7 Operaciones

- Tareas programadas: cancelación de pedidos sin confirmar, reinicio diario de porciones y limpieza de tokens vencidos.
- Colas: avisos, acreditación de puntos, resúmenes de IA y eventos de calendario.
- Respaldo diario de la base de datos.

#### 2.1.8 Requisitos de adaptación al sitio

- Todas las sodas están en Costa Rica: las horas se interpretan en `America/Costa_Rica`.
- La moneda es el colón.
- El idioma es el español.

### 2.2 Funciones del producto

| Área | Funciones principales |
|---|---|
| Menú | Publicar el menú del día por soda, con categorías, precios, fotos, porciones y horario. |
| Cuentas y acceso | Registro de clientes, ingreso con token, tres roles con permisos distintos y derechos sobre los datos personales. |
| Pedidos | Realizar, consultar y cancelar pedidos sin vender de más y con precio congelado. |
| Cocina | Ver los pedidos de su soda en tiempo real y avanzarlos por sus estados. |
| Pagos | Cobrar por adelantado, con el monto calculado por el servidor y confirmado por webhook. |
| Lealtad | Acumular y canjear puntos sin que el saldo quede negativo. |
| Automatización | Cancelar lo que nadie confirmó, reiniciar porciones y avisar al cliente. |
| Lista de espera | Apartar una porción liberada al siguiente cliente en la fila, por tiempo limitado. |
| Inteligencia artificial | Convertir la pizarra en platos validados y resumir las reseñas. |
| Reportes | Ventas por hora para decidir qué cocinar y cuándo promocionar. |
| Operación | Salud, monitoreo, registros, respaldos y despliegue automático. |

### 2.3 Características de los usuarios

| Usuario | Descripción | Competencia digital | Implicaciones de diseño |
|---|---|---|---|
| Visitante | Persona que quiere saber qué hay de almuerzo. | Variable. | El menú es público y carga rápido en el celular. |
| Cliente | Trabajadores, estudiantes y vecinos que piden para llevar. | Media: usan apps de mensajería y pago. | Flujo corto de pedido y pago; estado visible del pedido. |
| Cocina | Personal que prepara los pedidos, con las manos ocupadas y poco tiempo. | Básica. | Pantalla que se actualiza sola, botones grandes y un solo toque por acción. |
| Dueño | Persona dueña de la soda. Administra desde el celular entre clientes. | Básica a media. | Carga del menú asistida por IA, formularios cortos y reportes simples. |
| Agente de IA | Asistente que consulta sodas y menús en nombre de una persona. | No aplica. | Solo lectura de datos públicos, con un contrato predecible. |
| Operador | Persona técnica que despliega y monitorea. | Alta. | Salud, tablero, registros estructurados y guías reproducibles. |

### 2.4 Restricciones

**Restricciones académicas:**

- El producto se desarrolla durante el III cuatrimestre de 2026, clase por clase, según el plan de clases.
- El sistema debe estar desplegado y accesible desde el miércoles 30 de setiembre de 2026.
- No se procesa dinero real, no se guardan datos de personas reales y el sistema no se comercializa.

**Restricciones de tecnología**, impuestas por el curso:

| Componente | Tecnología obligatoria |
|---|---|
| API | Laravel 13 sobre PHP 8.4 o superior, en modo API. |
| Base de datos | PostgreSQL 18 en todos los ambientes: desarrollo, pruebas y producción. |
| Cliente | Vue 3 con TypeScript, en un repositorio aparte. |
| Autenticación | Laravel Sanctum. |
| Documentación | Scramble, que genera el contrato OpenAPI desde el código. |
| Pruebas y calidad | PHPUnit, Laravel Pint y Larastan. |
| Tiempo real | Laravel Reverb. |
| Repositorio y CI | GitHub y GitHub Actions. |

**Restricciones de proceso:**

- Todo cambio entra por pull request revisado y con la integración continua en verde.
- Cada clase se trabaja en una rama `clase-NN` y termina con la etiqueta `clase-NN`.

**Restricciones regulatorias:** Ley 8968 de protección de datos personales.

**Restricciones de presupuesto:**

- Se usan servicios en modo de prueba o en su capa gratuita.
- La clave del proveedor de IA tiene un tope de gasto configurado.

### 2.5 Suposiciones y dependencias

| ID | Suposición o dependencia | Si no se cumple |
|---|---|---|
| S-01 | Stripe permite operar en modo de prueba desde Costa Rica sin activar la cuenta. | Se usa Stripe solo para webhooks a partir de la clase 17 (plan de clases, riesgo 5). |
| S-02 | La soda tiene conexión a internet durante el horario de atención. | Pedidos y pantalla de cocina no están disponibles; la soda vuelve temporalmente al cuaderno. |
| S-03 | Existe una plataforma de despliegue con HTTPS disponible para el curso. | Se despliega con contenedores en un servidor virtual propio. |
| S-04 | Meta for Developers entrega un número de prueba de WhatsApp y aprueba una plantilla. | Los avisos se envían por correo o se registran en el log. |
| S-05 | Google Cloud permite una pantalla de consentimiento en modo de prueba con usuarios de prueba. | El extra de Google Calendar queda fuera. |
| S-06 | El proveedor de IA mantiene disponible un modelo con entrada de imagen y salida estructurada. | El dueño carga los platos a mano (RF-IA-04). |

### 2.6 Requisitos pospuestos

Se documentan para versiones posteriores al curso:

| ID | Requisito | Motivo del aplazamiento |
|---|---|---|
| POS-01 | Cobro con SINPE Móvil. | Requiere ONVO Pay u otro proveedor local; es parte de la Investigación 1 de los equipos. |
| POS-02 | Factura electrónica versión 4.4 del Ministerio de Hacienda. | Complejidad regulatoria fuera del alcance del curso. |
| POS-03 | Registro de pagos en efectivo al retirar. | La clienta prioriza el cobro por adelantado para reducir pedidos no recogidos. |
| POS-04 | Entregas a domicilio. | Otro modelo de negocio, con repartidores y zonas. |
| POS-05 | Aplicación móvil nativa. | El cliente web responsivo cubre la necesidad. |
| POS-06 | Varias sucursales de una misma soda. | Ninguna soda del piloto tiene sucursales. |
| POS-07 | Inventario de ingredientes. | La soda controla porciones, no ingredientes. |

---

## 3. Requisitos específicos

### 3.1 Requisitos de interfaces externas

#### 3.1.1 Interfaces de usuario

| ID | Requisito | Prioridad | Clase |
|---|---|---|---|
| IU-01 | El cliente web deberá mostrar el menú público de una soda con categorías, platos, precios, foto, porciones disponibles y si la soda está abierta en este momento. | Esencial | 12 |
| IU-02 | El cliente web deberá permitir armar un carrito, confirmar el pedido, pagarlo y seguir su estado hasta que esté listo. | Esencial | 17, 18 |
| IU-03 | El cliente web deberá ofrecer una pantalla de cocina que muestre los pedidos de la soda por estado y permita avanzarlos con un toque. | Esencial | 21 |
| IU-04 | El cliente web deberá ofrecer al dueño pantallas para administrar platos, categorías, horario, promociones y personal, y para ver reportes. | Esencial | 17 |
| IU-05 | Toda vista que consulte la API deberá mostrar un estado de carga, un estado de error con un mensaje útil y un estado vacío cuando no haya datos. | Esencial | 17 |
| IU-06 | Los errores de la API (RFC 9457) deberán mostrarse como mensajes en español comprensibles para la persona usuaria, nunca como códigos técnicos. | Esencial | 18 |
| IU-07 | Los montos deberán mostrarse en colones con separador de miles y sin decimales, por ejemplo ₡2 800. | Esencial | 12 |
| IU-08 | La API deberá publicar su documentación interactiva con Swagger UI en `/docs/api`. | Esencial | 5, 16 |

#### 3.1.2 Interfaces de hardware

| ID | Requisito | Prioridad | Clase |
|---|---|---|---|
| IH-01 | El sistema no deberá requerir hardware distinto de un teléfono, una tableta o una computadora con navegador moderno. | Esencial | 12 |
| IH-02 | El cliente web deberá permitir tomar la foto de la pizarra con la cámara del celular o elegir una imagen guardada. | Deseable | 18 |

#### 3.1.3 Interfaces de software

| ID | Sistema externo | Propósito | Mecanismo | Prioridad | Clase |
|---|---|---|---|---|---|
| IS-01 | Stripe, en modo de prueba | Cobrar pedidos y notificar el resultado. | API REST por HTTP directo con claves `sk_test_`, y webhooks firmados con HMAC SHA-256. | Esencial | 11, 17 |
| IS-02 | Proveedor de IA (Anthropic Claude) | Leer la pizarra y resumir reseñas. | API con salida estructurada. La clave vive en el servidor. | Esencial | 18, 20 |
| IS-03 | Servidor de correo (SMTP) | Avisos al cliente. | Canal de aviso por correo. | Esencial | 11 |
| IS-04 | WhatsApp Business Cloud (Meta) | Aviso de pedido listo. | Plantillas enviadas desde la cola; webhook entrante firmado. | Deseable | 19 |
| IS-05 | Google Calendar | Sincronizar encargos con fecha. | OAuth 2 con código de autorización y PKCE. | Deseable | 16 |
| IS-06 | Clientes MCP (asistentes de IA) | Consultar sodas y menús. | Servidor MCP de solo lectura. | Deseable | 22 |
| IS-07 | PostgreSQL 18 | Persistencia. | Conexión `pgsql`. | Esencial | 2 |
| IS-08 | Redis | Caché y colas en producción. | Conexión `redis`. | Esencial | 23 |

#### 3.1.4 Interfaces de comunicaciones

| ID | Requisito | Prioridad | Clase |
|---|---|---|---|
| IC-01 | Toda comunicación con la API en producción deberá usar HTTPS con TLS 1.2 o superior. | Esencial | 16, 24 |
| IC-02 | La API deberá recibir y responder JSON en UTF-8. Los errores deberán responder `application/problem+json`. | Esencial | 5 |
| IC-03 | La pantalla de cocina deberá recibir los eventos por WebSocket seguro (`wss`), en un canal privado por soda. | Esencial | 21 |
| IC-04 | El estado de un pedido deberá poder seguirse desde el cliente web mediante SSE. | Deseable | 19 |

#### 3.1.5 Interfaz de programación: contrato inicial de la API

Contrato diseñado antes de programar. El contrato vigente es el que genera Scramble y se versiona en `openapi/v1.json`.

| Método | Ruta | Quién | Respuestas |
|---|---|---|---|
| GET | `/api/v1/sodas/{soda}/platos` | Visitante | 200, 404 |
| GET | `/api/v1/sodas/{soda}/platos/{plato}` | Visitante | 200, 404 |
| POST | `/api/v1/tokens` | Cliente, personal | 201, 422, 429 |
| DELETE | `/api/v1/tokens/actual` | Autenticado | 204, 401 |
| GET | `/api/v1/cocina/platos` | Cocina, dueño | 200, 401, 403 |
| POST | `/api/v1/cocina/platos` | Dueño | 201, 401, 403, 422 |
| PATCH | `/api/v1/cocina/platos/{plato}` | Dueño | 200, 401, 403, 404, 422 |
| POST | `/api/v1/sodas/{soda}/pedidos` | Cliente | 201, 401, 404, 409, 422 |
| GET | `/api/v1/pedidos/{pedido}` | Cliente dueño del pedido, personal de la soda | 200, 401, 404 |
| POST | `/api/v1/pedidos/{pedido}/cancelacion` | Cliente dueño del pedido | 200, 401, 404, 409 |
| POST | `/api/v1/pedidos/{pedido}/pago` | Cliente dueño del pedido | 201, 401, 404, 409, 503 |
| GET | `/api/v1/cocina/pedidos` | Cocina, dueño | 200, 401, 403 |
| PATCH | `/api/v1/cocina/pedidos/{pedido}/estado` | Cocina, dueño | 200, 401, 403, 404, 409 |

### 3.2 Requisitos funcionales

#### 3.2.1 Menú y catálogo (RF-MEN)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-MEN-01 | El sistema deberá permitir a cualquier visitante, sin ingresar, consultar el menú de una soda: platos disponibles agrupados por categoría, con nombre, descripción, precio vigente, foto, tiempo de preparación y porciones disponibles. | Esencial | N-01 | 5 |
| RF-MEN-02 | El sistema deberá permitir al dueño crear, editar y desactivar platos de **su** soda, con nombre (máximo 120 caracteres), precio en colones enteros (₡100 a ₡100 000), tiempo de preparación (1 a 120 minutos), porciones disponibles (0 a 500) y categoría. | Esencial | N-04 | 4, 8 |
| RF-MEN-03 | El sistema deberá rechazar con 422 un plato cuya categoría pertenezca a otra soda. | Esencial | RT | 8 |
| RF-MEN-04 | El sistema deberá permitir al dueño administrar las categorías de su soda. Al eliminar una categoría, sus platos deberán quedar sin categoría, sin eliminarse. | Esencial | N-01 | 7 |
| RF-MEN-05 | El sistema deberá permitir al dueño definir el horario de atención por día de la semana, con hora de apertura (incluida) y de cierre (excluida). | Esencial | N-01 | 7 |
| RF-MEN-06 | El sistema deberá indicar en el menú público si la soda está abierta en este momento, según su horario en la zona `America/Costa_Rica`. | Esencial | N-01 | 7 |
| RF-MEN-07 | El sistema deberá permitir al dueño subir una foto por plato y el logo de la soda en JPG, PNG o WebP, de máximo 2 MB y 4000 × 4000 píxeles, validando el contenido real del archivo y no solo su extensión. | Esencial | N-01 | 9 |
| RF-MEN-08 | El sistema deberá guardar cada archivo subido con un nombre generado por el servidor, nunca con el nombre que envía el navegador. | Esencial | RT | 9 |
| RF-MEN-09 | El sistema deberá permitir al dueño definir una cantidad base de porciones diarias por plato. | Esencial | N-02 | 10 |
| RF-MEN-10 | El sistema deberá mostrar el menú público desde caché e invalidar esa caché cada vez que el dueño modifique un plato, una categoría, el horario o una promoción de la soda. | Esencial | RT | 11, 12 |

#### 3.2.2 Cuentas, acceso y roles (RF-CUE)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-CUE-01 | El sistema deberá permitir que una persona se registre como cliente con nombre, correo, contraseña y teléfono opcional. No deberá crear la cuenta sin la aceptación explícita de los términos, y deberá guardar la fecha de esa aceptación. | Esencial | N-19 | 14 |
| RF-CUE-02 | El sistema deberá permitir ingresar con correo y contraseña, indicando el nombre del dispositivo, y entregar un token con las abilities del rol de la persona. | Esencial | N-09 | 8, 14 |
| RF-CUE-03 | El sistema deberá permitir cerrar la sesión actual, revocando su token, y cerrar todas las sesiones de la persona. | Esencial | RT | 8, 14 |
| RF-CUE-04 | El sistema deberá manejar tres roles: **cliente** (ability `pedidos`), **cocina** (`pedidos`, `cocina`) y **dueño** (`pedidos`, `cocina`, `administrar`). | Esencial | N-09 | 14 |
| RF-CUE-05 | El sistema deberá asociar cada persona del personal a una sola soda. Los clientes no pertenecen a ninguna. | Esencial | N-08 | 8 |
| RF-CUE-06 | El sistema deberá permitir al dueño agregar y desactivar personal de cocina de su soda. | Deseable | N-09 | 14 |
| RF-CUE-07 | El sistema deberá permitir a cada persona exportar todos los datos que SodaYa guarda sobre ella (derecho de acceso, Ley 8968). | Esencial | N-19 | 13 |
| RF-CUE-08 | El sistema deberá permitir a cada persona corregir su nombre y su teléfono. Nunca deberá permitirle cambiar su rol ni su soda (derecho de rectificación). | Esencial | N-19 | 13 |
| RF-CUE-09 | El sistema deberá permitir a cada persona eliminar su cuenta. La cuenta se deberá anonimizar y sus pedidos se conservarán como registro contable de la soda, sin poder asociarse a la persona (derecho de supresión). | Esencial | N-19 | 13 |

#### 3.2.3 Sodas y aislamiento entre inquilinos (RF-SOD)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-SOD-01 | El sistema deberá permitir que varias sodas operen en la misma instalación con sus datos completamente separados. | Esencial | N-08 | 8 |
| RF-SOD-02 | El sistema deberá determinar la soda del personal a partir del usuario autenticado. Nunca deberá tomarla de la URL, de los parámetros ni del cuerpo de la petición. | Esencial | N-08 | 8 |
| RF-SOD-03 | Toda consulta de personal sobre platos, categorías, horarios, promociones, pedidos y reportes deberá limitarse automáticamente a su soda. | Esencial | N-08 | 8 |
| RF-SOD-04 | Al crear un registro que pertenece a una soda, el sistema deberá asignar la soda del usuario autenticado aunque la petición envíe otra. | Esencial | N-08 | 8 |
| RF-SOD-05 | Ante un recurso de otra soda, el sistema deberá responder 404, sin revelar que el recurso existe. | Esencial | RT | 8, 15 |

#### 3.2.4 Pedidos (RF-PED)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-PED-01 | El sistema deberá permitir a un cliente autenticado realizar un pedido para llevar en una soda, con entre 1 y 20 líneas y una cantidad de 1 a 20 por línea. Un pedido sin líneas deberá responder 422 `pedido-vacio`, y una cantidad fuera de rango, 422 `cantidad-invalida`. | Esencial | N-01, N-02 | 11 |
| RF-PED-02 | El sistema deberá rechazar con 409 `soda-cerrada` un pedido hecho fuera del horario de la soda. | Esencial | N-02 | 7 |
| RF-PED-03 | El sistema deberá rechazar con 422 `plato-no-pertenece-a-la-soda` un pedido con un plato de otra soda. | Esencial | RT | 11 |
| RF-PED-04 | El sistema deberá rechazar con 409 `plato-no-disponible` un pedido con un plato desactivado. | Esencial | N-02 | 11 |
| RF-PED-05 | El sistema deberá rechazar con 409 `porciones-agotadas` un pedido cuya cantidad supere las porciones que quedan. | Esencial | N-02 | 10 |
| RF-PED-06 | El sistema deberá garantizar que dos clientes que piden la última porción al mismo tiempo no la obtengan ambos: uno recibe 201 y el otro 409. Las porciones nunca deberán quedar en negativo. | Esencial | N-02 | 10 |
| RF-PED-07 | El sistema deberá descontar las porciones y guardar el pedido en una sola transacción: o se hacen ambas cosas o ninguna. | Esencial | N-02 | 10 |
| RF-PED-08 | El sistema deberá congelar en cada línea el nombre y el precio vigente del plato al momento de pedir, incluido el descuento de una hora feliz activa. | Esencial | N-04 | 4, 12 |
| RF-PED-09 | El sistema deberá calcular el total en el servidor a partir de las líneas guardadas. Deberá ignorar cualquier precio o total enviado por el navegador. | Esencial | N-04 | 11 |
| RF-PED-10 | El sistema deberá identificar cada pedido con un UUID versión 7 y responder 201 con el encabezado `Location`. | Esencial | RT | 11 |
| RF-PED-11 | El sistema deberá permitir al cliente consultar sus pedidos y el detalle de cada uno. Un pedido ajeno deberá responder 404. | Esencial | N-04 | 11, 15 |
| RF-PED-12 | El sistema deberá permitir al cliente cancelar su pedido solo mientras esté pendiente o confirmado. En otro estado deberá responder 409. | Esencial | N-03 | 11 |
| RF-PED-13 | Al cancelarse un pedido, por el motivo que sea, el sistema deberá devolver sus porciones al menú en la misma transacción. | Esencial | N-02 | 9 |
| RF-PED-14 | Cada pedido deberá incluir en su respuesta los enlaces a las acciones disponibles en su estado actual, por ejemplo pagar o cancelar. | Deseable | RT | 11 |

#### 3.2.5 Cocina (RF-COC)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-COC-01 | El sistema deberá permitir a la cocina y al dueño listar los pedidos de su soda, filtrados por estado y paginados. | Esencial | N-05 | 11 |
| RF-COC-02 | El sistema deberá permitir a la cocina avanzar un pedido solo en este orden: pendiente → confirmado → listo → entregado. Un salto o un retroceso deberá responder 409 `transicion-no-permitida`. | Esencial | N-05 | 9 |
| RF-COC-03 | El sistema deberá permitir a la cocina cancelar un pedido pendiente o confirmado, por ejemplo cuando se acabó un ingrediente. | Esencial | N-05 | 9 |
| RF-COC-04 | El sistema deberá registrar la fecha y hora de cada cambio de estado de un pedido. | Esencial | N-04 | 9 |

#### 3.2.6 Pagos (RF-PAG)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-PAG-01 | El sistema deberá permitir al cliente pagar su pedido por adelantado con la pasarela, en modo de prueba. | Esencial | N-03 | 11 |
| RF-PAG-02 | El sistema deberá calcular el monto a cobrar a partir del pedido guardado. Nunca deberá aceptar un monto enviado por el navegador. | Esencial | N-03 | 11 |
| RF-PAG-03 | El sistema deberá rechazar con 409 `pedido-ya-pagado` el intento de pagar un pedido ya pagado. | Esencial | N-03 | 11 |
| RF-PAG-04 | El sistema deberá enviar una clave de idempotencia en cada cobro, de modo que oprimir "pagar" dos veces no cobre dos veces. | Esencial | N-03 | 11 |
| RF-PAG-05 | El sistema deberá manejar de forma explícita los tres desenlaces de un cobro: aprobado, rechazado y error de comunicación. Ante un error de comunicación, el pedido deberá quedar con pago pendiente y responder 503. | Esencial | N-03 | 11 |
| RF-PAG-06 | El sistema deberá registrar el pago y actualizar el pedido en una sola transacción, guardando el proveedor, su referencia, el monto y el estado. | Esencial | RT | 11 |
| RF-PAG-07 | El sistema deberá confirmar el pago consultando su estado al proveedor (clase 11) y, desde la clase 17, mediante un webhook verificado. Nunca deberá confiar en el estado que reporta el navegador. | Esencial | RT | 11, 17 |
| RF-PAG-08 | El webhook de pagos deberá verificar la firma del proveedor, rechazar eventos con más de 5 minutos de antigüedad, procesar cada evento una sola vez y responder 2xx de inmediato, dejando el procesamiento en la cola. | Esencial | RT | 17 |
| RF-PAG-09 | El sistema deberá reembolsar automáticamente el pago de un pedido pagado que se cancela. | Opcional | N-03 | Por asignar |

#### 3.2.7 Puntos de lealtad (RF-LEA)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-LEA-01 | El sistema deberá acreditar al cliente 1 punto por cada ₡1000 del total de cada pedido entregado. | Deseable | N-06 | 10 |
| RF-LEA-02 | El sistema deberá acreditar los puntos de un pedido una sola vez, aunque el trabajo en cola se ejecute más de una vez. | Deseable | N-06 | 10 |
| RF-LEA-03 | El sistema deberá calcular el saldo como la suma de un libro de movimientos (compras y canjes). No deberá guardarlo como un campo editable. | Deseable | RT | 10 |
| RF-LEA-04 | El sistema deberá permitir al cliente canjear puntos. Si el saldo no alcanza, deberá responder 409 `puntos-insuficientes`. El saldo nunca deberá quedar en negativo, aunque lleguen dos canjes al mismo tiempo. | Deseable | N-06 | 10 |
| RF-LEA-05 | El sistema deberá permitir al cliente consultar su saldo y sus últimos 20 movimientos. | Deseable | N-06 | 10 |

#### 3.2.8 Procesos automáticos (RF-AUT)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-AUT-01 | El sistema deberá cancelar automáticamente, 10 minutos después de creado, todo pedido que la cocina no haya confirmado, devolviendo sus porciones y avisando al cliente. | Esencial | N-03 | 10 |
| RF-AUT-02 | Como red de seguridad ante trabajos perdidos, una tarea programada deberá revisar cada minuto los pedidos pendientes vencidos y cancelarlos, sin ejecutarse dos veces en paralelo. | Esencial | RT | 10 |
| RF-AUT-03 | El sistema deberá restablecer cada día a las 5:00 (hora de Costa Rica) las porciones de cada plato a su cantidad base. | Esencial | N-02 | 10 |
| RF-AUT-04 | El sistema deberá eliminar una vez al día los tokens vencidos. | Esencial | RT | 14 |
| RF-AUT-05 | Los trabajos lentos (avisos, acreditación de puntos, resúmenes de IA y eventos de calendario) deberán ejecutarse en cola, sin hacer esperar a quien hizo la petición. | Esencial | RT | 10 |

#### 3.2.9 Avisos al cliente (RF-AVI)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-AVI-01 | El sistema deberá avisar al cliente cuando su pedido esté listo y cuando se cancele. | Esencial | N-12 | 10, 11 |
| RF-AVI-02 | El canal de aviso deberá seleccionarse por configuración, entre log, correo y WhatsApp, sin modificar el código que decide cuándo avisar. | Esencial | RT | 11 |
| RF-AVI-03 | El sistema deberá enviar el aviso de pedido listo por WhatsApp Business Cloud con una plantilla aprobada. | Deseable | N-12 | 19 |
| RF-AVI-04 | El webhook entrante de WhatsApp deberá responder la verificación de Meta, validar la firma `X-Hub-Signature-256` y procesar cada mensaje una sola vez. | Deseable | RT | 19 |
| RF-AVI-05 | Un aviso que falle deberá reintentarse con espera creciente y registrarse en el log, sin afectar el pedido. | Esencial | RT | 19 |

#### 3.2.10 Lista de espera (RF-ESP)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-ESP-01 | El sistema deberá permitir a un cliente anotarse en la lista de espera de un plato agotado, una sola vez por plato. | Deseable | N-13 | 19 |
| RF-ESP-02 | Cuando se liberen porciones de un plato, por una cancelación o porque el dueño las repone, el sistema deberá apartarlas para los primeros de la fila, en orden de inscripción, y avisarles. | Deseable | N-13 | 19 |
| RF-ESP-03 | La porción apartada deberá reservarse 10 minutos. Si el cliente no pide en ese plazo, deberá ofrecerse al siguiente de la fila. | Deseable | N-13 | 19 |
| RF-ESP-04 | Dos ofertas simultáneas nunca deberán apartar la misma porción. | Deseable | RT | 19 |

#### 3.2.11 Tiempo real (RF-VIV)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-VIV-01 | La pantalla de cocina deberá mostrar sin recargar los pedidos nuevos, los cancelados y los cambios de estado de su soda. | Esencial | N-05 | 21 |
| RF-VIV-02 | Solo el personal de la soda deberá poder suscribirse al canal privado de esa soda. | Esencial | N-08 | 21 |
| RF-VIV-03 | Si se pierde la conexión, la pantalla deberá indicarlo, reconectarse sola y recargar el estado actual al reconectarse. | Esencial | N-05 | 21 |
| RF-VIV-04 | El cliente deberá ver el cambio de estado de su pedido sin recargar la página. | Deseable | N-12 | 19 |

#### 3.2.12 Inteligencia artificial (RF-IA)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-IA-01 | El sistema deberá permitir al dueño enviar una foto de la pizarra (JPG, PNG o WebP de máximo 5 MB) o su texto (máximo 5000 caracteres) y recibir una propuesta de platos con nombre, precio y categoría. | Esencial | N-11 | 18 |
| RF-IA-02 | La propuesta de la IA deberá ser datos estructurados, validados con las mismas reglas de RF-MEN-02 antes de mostrarse. Los elementos que no cumplan deberán descartarse o marcarse para revisión. | Esencial | N-11 | 18 |
| RF-IA-03 | El sistema nunca deberá guardar platos por su cuenta: el dueño revisa la propuesta y confirma cada plato. | Esencial | N-11 | 18 |
| RF-IA-04 | Si el proveedor de IA no responde, rechaza la solicitud o devuelve algo inservible, el sistema deberá responder 503 `servicio-no-disponible`, y el dueño deberá poder cargar los platos a mano. | Esencial | N-11 | 18 |
| RF-IA-05 | El sistema deberá registrar por cada solicitud a la IA los tokens consumidos, el costo estimado y el tiempo de respuesta. | Esencial | RT | 18 |
| RF-IA-06 | Solo el dueño deberá poder usar la lectura de la pizarra. | Esencial | N-09 | 18 |

#### 3.2.13 Reportes (RF-REP)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-REP-01 | El sistema deberá mostrar al dueño las ventas de su soda por hora para una fecha: cantidad de pedidos y total vendido. Deberá contar solo los pedidos entregados y agrupar por hora de Costa Rica. | Deseable | N-10 | 9 |
| RF-REP-02 | El reporte deberá permitir filtrar las horas con un mínimo de pedidos. | Deseable | N-10 | 9 |

#### 3.2.14 Extras (RF-EXT)

Mejoran el producto, pero no son parte del núcleo. Se construyen como demostración en la clase indicada.

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-EXT-01 | **Hora feliz.** El sistema deberá permitir al dueño definir promociones para toda su soda, por día de la semana y franja horaria, con un descuento de 1 % a 90 %. | Deseable | N-07 | 12 |
| RF-EXT-02 | El sistema deberá rechazar con 409 `promocion-traslapada` una promoción que se traslape con otra de la misma soda el mismo día. | Deseable | RT | 12 |
| RF-EXT-03 | El precio vigente deberá calcularse en el servidor aplicando la promoción activa y redondeando al colón más cercano, sin usar números de punto flotante. | Deseable | N-07 | 12 |
| RF-EXT-04 | **Propinas.** El sistema deberá permitir al cliente agregar una propina de ₡0 a ₡50 000 al pagar, validada en el servidor e incluida en el cobro. | Deseable | N-15 | 14 |
| RF-EXT-05 | **Comisiones.** El sistema deberá dirigir el cobro a la cuenta conectada de la soda y retener una comisión del 5 % del total del pedido, sin incluir la propina. | Deseable | N-15 | 14 |
| RF-EXT-06 | El reparto de cada cobro deberá registrarse en colones enteros. El residuo del redondeo deberá quedar para la soda y la suma de las partes deberá ser siempre igual al total cobrado. | Deseable | RT | 14 |
| RF-EXT-07 | **Google Calendar.** El sistema deberá permitir al dueño conectar su calendario y sincronizar los encargos con fecha como eventos, sin duplicarlos. | Deseable | N-14 | 16 |
| RF-EXT-08 | **Reseñas.** El sistema deberá permitir al cliente dejar una sola reseña por pedido entregado, con 1 a 5 estrellas y un comentario opcional. | Deseable | N-16 | 20 |
| RF-EXT-09 | El sistema deberá generar en cola un resumen estructurado de las reseñas de cada soda (fortalezas, quejas y tono) y mostrarlo junto con el promedio de estrellas. | Deseable | N-16 | 20 |
| RF-EXT-10 | **Servidor MCP.** El sistema deberá ofrecer a los asistentes de IA herramientas de solo lectura para listar sodas y consultar su menú. No deberá exponer pedidos, clientes ni tokens. | Deseable | N-17 | 22 |

#### 3.2.15 Operación (RF-OPE)

| ID | Requisito | Prioridad | Origen | Clase |
|---|---|---|---|---|
| RF-OPE-01 | El sistema deberá ofrecer una ruta de salud `/up` que responda 200 solo si la aplicación, la base de datos y la caché funcionan, y 503 en caso contrario. | Esencial | N-18 | 21 |
| RF-OPE-02 | El sistema deberá ofrecer un tablero de monitoreo con solicitudes lentas, consultas lentas, excepciones, colas y uso por persona, protegido con credenciales. | Esencial | N-18 | 21 |
| RF-OPE-03 | El sistema deberá incluir en cada respuesta el encabezado `X-Request-Id`, y repetir ese identificador en el campo `instance` de los errores y en cada línea de log. | Esencial | RT | 5, 21 |

### 3.3 Requisitos de rendimiento

Las metas se miden con una prueba de carga en una copia del sistema con la configuración de producción, con 5 sodas y 200 platos de datos de prueba.

| ID | Requisito | Métrica y criterio de aceptación | Clase |
|---|---|---|---|
| RNF-REN-01 | Consulta del menú público. | p95 ≤ 300 ms con 50 usuarios concurrentes durante 5 minutos, sin errores. | 11, 23 |
| RNF-REN-02 | Realizar un pedido. | p95 ≤ 800 ms con 30 pedidos por minuto sobre los mismos platos, sin porciones negativas ni errores 5xx. | 10, 23 |
| RNF-REN-03 | Consultas por petición. | Ningún listado ejecuta consultas N+1: la cantidad de consultas no crece con la cantidad de elementos. La carga diferida está prohibida en desarrollo y pruebas. | 9 |
| RNF-REN-04 | Paginación. | Todo listado va paginado, con 15 elementos por defecto y 50 como máximo. | 9 |
| RNF-REN-05 | Tamaño de respuesta. | Una página de un listado pesa 100 KB o menos, sin contar imágenes. | 9 |
| RNF-REN-06 | Llamadas a proveedores externos. | Toda llamada tiene tiempo de espera definido: 10 s para pagos, mensajería y calendario, y 60 s por intento, con 2 reintentos, para la IA. | 11, 18 |
| RNF-REN-07 | Tiempo real. | Un pedido nuevo aparece en la pantalla de cocina en 2 s o menos desde su creación. | 21 |
| RNF-REN-08 | Capacidad. | El sistema soporta 20 sodas y 2000 pedidos diarios en total sin cambiar la infraestructura. | 23 |
| RNF-REN-09 | Índices. | Las consultas del reporte de ventas y de los pedidos de cocina usan índice, según `EXPLAIN ANALYZE`, con 10 000 pedidos de prueba. | 9 |

### 3.4 Requisitos lógicos de la base de datos

**Entidades principales.** Esquema en tercera forma normal.

| Entidad | Datos principales | Reglas de integridad |
|---|---|---|
| sodas | nombre, teléfono, logo, cuenta de pago conectada | Nombre obligatorio. |
| users | nombre, correo, contraseña con hash, teléfono cifrado, rol, soda, fecha de aceptación de términos | Correo único. El personal tiene soda y el cliente no. |
| categorias | soda, nombre | Nombre único por soda. |
| platos | soda, categoría, nombre, descripción, precio, minutos de preparación, porciones disponibles, porciones base, foto, activo | `CHECK` de precio entre 100 y 100 000, minutos entre 1 y 120, y porciones ≥ 0. La categoría debe ser de la misma soda. |
| horarios | soda, día de la semana (1 a 7), abre, cierra | `abre < cierra`. |
| pedidos | id UUID v7, soda, cliente, estado, fechas de cada estado | Estado dentro de los cinco valores. |
| lineas_pedido | pedido, plato, nombre del plato, precio unitario, cantidad | Cantidad entre 1 y 20. Llave foránea compuesta: el plato debe ser de la soda del pedido. |
| pagos | pedido, proveedor, referencia, monto, propina, comisión, estado, clave de idempotencia | Referencia y clave de idempotencia únicas. |
| movimientos_puntos | cliente, pedido, puntos, motivo | Motivo `compra` o `canje`. Índice único parcial: una sola compra por pedido. |
| promociones | soda, día, desde, hasta, porcentaje | Porcentaje entre 1 y 90. Restricción `EXCLUDE`: sin traslapes por soda y día. |
| lista_de_espera | plato, cliente, fecha, apartado hasta | Única por plato y cliente. |
| resenas | pedido, estrellas, comentario | Una por pedido. Estrellas entre 1 y 5. |
| eventos_webhook | proveedor, id del evento, fecha | Id del evento único por proveedor. |

**Reglas generales de datos:**

- El dinero se guarda en colones enteros, nunca con decimales ni punto flotante.
- Las fechas se guardan en UTC y se presentan en la hora de Costa Rica.
- Las restricciones de la base de datos (`CHECK`, llaves foráneas, `UNIQUE`, `EXCLUDE`) son la última defensa: garantizan la integridad aunque el código falle.
- El total del pedido y el saldo de puntos no se guardan: se calculan a partir de las líneas y de los movimientos.
- Los pedidos y los pagos se conservan como registro contable de la soda. Al suprimir una cuenta, sus pedidos se desvinculan de la persona.

### 3.5 Restricciones de diseño

#### 3.5.1 Cumplimiento de estándares

| ID | Requisito | Verificación | Clase |
|---|---|---|---|
| RD-01 | Todo error deberá responder en formato RFC 9457 con `type`, `title`, `status`, `detail` e `instance`. Los valores de `type` deberán pertenecer a la lista cerrada de la tabla siguiente. | Pruebas de funcionalidad de cada código. | 5 |
| RD-02 | La API deberá versionarse en la URL (`/api/v1`). Un cambio incompatible deberá publicarse como una versión nueva. | Revisión del contrato. | 5 |
| RD-03 | El contrato OpenAPI 3.1 deberá generarse desde el código, versionarse en `openapi/v1.json` y verificarse con una prueba que falle si el contrato y el código difieren. | Prueba automatizada. | 5, 16 |
| RD-04 | Los recursos, rutas y atributos deberán nombrarse en español con el vocabulario del negocio: rutas en plural y atributos en `snake_case`. | Revisión de código. | 3 |
| RD-05 | Las fechas deberán viajar en ISO 8601 con zona horaria. | Pruebas. | 5 |
| RD-06 | Cada operación del contrato deberá tener un `operationId` estable. | Revisión del contrato. | 22 |

**Tipos de error de la API.** El `type` es una URI con la forma `https://<dominio de la API>/problemas/<código>`.

| Código | HTTP | Cuándo |
|---|---|---|
| `solicitud-invalida` | 400 | La solicitud no se puede procesar tal como viene. |
| `no-autenticado` | 401 | Sin token o con un token vencido. |
| `prohibido` | 403 | El token no tiene la ability requerida. |
| `no-encontrado` | 404 | No existe, o pertenece a otra soda o a otra persona. |
| `pedido-no-encontrado` | 404 | El pedido no existe o no es de quien lo consulta. |
| `metodo-no-permitido` | 405 | La ruta no acepta el método HTTP usado. |
| `datos-invalidos` | 422 | La forma de los datos no es válida. Incluye el detalle por campo. |
| `pedido-vacio` | 422 | El pedido no trae ninguna línea. |
| `cantidad-invalida` | 422 | La cantidad de una línea está fuera de 1 a 20. |
| `plato-no-pertenece-a-la-soda` | 422 | El plato es de otra soda. |
| `soda-cerrada` | 409 | Fuera del horario de atención. |
| `plato-no-disponible` | 409 | El plato está desactivado. |
| `porciones-agotadas` | 409 | No quedan porciones suficientes. |
| `transicion-no-permitida` | 409 | El cambio de estado no aplica. |
| `pedido-ya-pagado` | 409 | El pedido ya tiene un pago aprobado. |
| `puntos-insuficientes` | 409 | El saldo no alcanza para el canje. |
| `promocion-traslapada` | 409 | La promoción se cruza con otra de la misma soda. |
| `conflicto` | 409 | Otro choque con el estado actual del recurso. |
| `demasiadas-solicitudes` | 429 | Se superó un límite de tasa. Incluye `Retry-After`. |
| `error-interno` | 500 | Error inesperado, sin detalles técnicos. |
| `servicio-no-disponible` | 503 | Un proveedor externo no respondió. |

**Regla para elegir el código:** 422 cuando la solicitud está mal formada y el cliente la corrige cambiando los datos. 409 cuando está bien formada pero choca con el estado actual del negocio.

#### 3.5.2 Restricciones de arquitectura

| ID | Requisito | Clase |
|---|---|---|
| RD-07 | El contexto de pedidos deberá seguir una arquitectura hexagonal ligera, con dominio, aplicación e infraestructura separados. El dominio no deberá depender de Laravel. | 11 |
| RD-08 | Cada proveedor externo (pago, avisos, IA, calendario) deberá integrarse detrás de una interfaz propia, resuelta por el contenedor de servicios. | 11 |
| RD-09 | Los proveedores de pago y de mensajería deberán consumirse por HTTP directo con el cliente `Http` de Laravel, sin SDK. | 11 |
| RD-10 | Las respuestas deberán construirse con API Resources que funcionen como lista blanca de campos. | 4 |
| RD-11 | La validación de entrada deberá hacerse con Form Requests, antes de llegar al controlador. | 4 |

### 3.6 Atributos del sistema de software

#### 3.6.1 Fiabilidad e integridad

| ID | Requisito | Verificación | Clase |
|---|---|---|---|
| RNF-FIA-01 | Las operaciones que cambian más de una tabla (pedido y porciones, pago y pedido, canje y saldo) deberán ejecutarse en una sola transacción. | Pruebas que fuerzan una falla a mitad de la operación. | 10, 11 |
| RNF-FIA-02 | Las operaciones con competencia por un recurso (última porción, canje de puntos, porción apartada) deberán usar bloqueo de filas en PostgreSQL. | Pruebas de concurrencia contra PostgreSQL. | 10, 19 |
| RNF-FIA-03 | Cobro, acreditación de puntos, webhooks y avisos deberán ser idempotentes. | Pruebas que repiten la operación. | 10, 11, 17 |
| RNF-FIA-04 | Si falla un proveedor externo, el sistema deberá degradarse sin caerse: el pago queda pendiente, la IA pasa a carga manual y el aviso se reintenta. | Pruebas que simulan la falla del proveedor. | 11, 18, 19 |
| RNF-FIA-05 | Las tareas en cola deberán reintentarse ante fallas transitorias. Los trabajos que agoten sus reintentos deberán registrarse como fallidos. | Revisión de configuración y pruebas. | 10 |

#### 3.6.2 Disponibilidad y operación

| ID | Requisito | Métrica o verificación | Clase |
|---|---|---|---|
| RNF-DIS-01 | Disponibilidad. | 99 % mensual entre las 6:00 y las 20:00, hora de Costa Rica. | 24 |
| RNF-DIS-02 | Despliegue sin interrupción. | Cada despliegue cambia de versión de forma atómica, sin respuestas 5xx durante el cambio. | 24 |
| RNF-DIS-03 | Despliegue automático. | Integrar a `main` con la CI en verde despliega a producción sin pasos manuales. | 24 |
| RNF-DIS-04 | Plan de reversión. | Se vuelve a la versión anterior en 5 minutos o menos, con un comando documentado. | 24 |
| RNF-DIS-05 | Migraciones compatibles. | Cada migración es reversible y funciona también con el código anterior (expandir y luego contraer). | 7, 24 |
| RNF-DIS-06 | Respaldos. | Respaldo diario de PostgreSQL con 7 días de retención y restauración probada al menos una vez por mes. RPO de 24 horas y RTO de 1 hora. | 24 |
| RNF-DIS-07 | Procesos supervisados. | Los workers de la cola, el scheduler y Reverb se reinician solos si se detienen. | 23, 24 |
| RNF-DIS-08 | Despliegue temprano. | El sistema está desplegado con dirección pública desde el 30/09/2026 y así se mantiene durante todo el curso. | 6 |

#### 3.6.3 Seguridad

| ID | Requisito | Verificación | Clase |
|---|---|---|---|
| RNF-SEG-01 | Los tokens deberán vencer 60 minutos después de emitidos y poder revocarse. | Prueba con viaje en el tiempo. | 14 |
| RNF-SEG-02 | Cada endpoint deberá verificar la ability del token, y cada recurso, la propiedad: el cliente ve solo sus pedidos, la cocina solo los de su soda y el dueño todo lo de su soda. | Matriz de permisos probada con proveedores de datos de PHPUnit. | 14, 15 |
| RNF-SEG-03 | El sistema deberá resistir los cuatro ataques demostrados en clase: BOLA (cambiar el id de un recurso ajeno), BFLA (un cliente usando un endpoint de cocina), asignación masiva (enviar precio, estado o soda en el cuerpo) y exposición excesiva de datos. | Pruebas automatizadas de cada ataque. | 13 |
| RNF-SEG-04 | Las contraseñas deberán guardarse con un hash adaptativo (bcrypt o Argon2id), con un mínimo de 8 caracteres. | Revisión y prueba. | 14 |
| RNF-SEG-05 | El teléfono del cliente deberá guardarse cifrado en la base de datos. | Revisión de la tabla. | 13 |
| RNF-SEG-06 | Límites de tasa: 60 solicitudes por minuto por persona o IP en general; 5 por minuto por correo y 20 por minuto por IP en el ingreso y el registro; 10 pedidos por minuto por persona. Al superarlos, responder 429 con `Retry-After`. | Pruebas de cada límite. | 16 |
| RNF-SEG-07 | En producción: HTTPS obligatorio con HSTS, encabezados de seguridad (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`) y CORS limitado al dominio del cliente web. | Prueba de encabezados y revisión de configuración. | 16 |
| RNF-SEG-08 | En producción, `APP_DEBUG` deberá estar apagado. Ningún error deberá exponer trazas, SQL, rutas de archivos ni nombres de clases. | Prueba que provoca un error 500. | 5, 16 |
| RNF-SEG-09 | Los webhooks deberán verificar la firma HMAC SHA-256 con comparación en tiempo constante y con una tolerancia de 5 minutos contra ataques de repetición. | Pruebas con cuerpo firmado, firma inválida y evento viejo. | 17, 19 |
| RNF-SEG-10 | Las credenciales deberán vivir en variables de entorno. El archivo `.env` nunca deberá versionarse, y `.env.example` deberá listar cada variable sin valores reales. | Revisión del repositorio y análisis de secretos. | 6 |
| RNF-SEG-11 | Solo deberán usarse credenciales de prueba de los proveedores de pago. | Revisión. | 11 |
| RNF-SEG-12 | Los datos de tarjeta nunca deberán pasar por el servidor de SodaYa. | Revisión del flujo de pago. | 18 |
| RNF-SEG-13 | La salida de la IA deberá tratarse como entrada no confiable. Un texto de la pizarra o de una reseña con instrucciones maliciosas no deberá alterar precios, sodas ni permisos. | Casos de prueba de inyección de prompt. | 18, 20 |
| RNF-SEG-14 | Las dependencias no deberán tener vulnerabilidades conocidas de severidad alta o crítica. | `composer audit` en la CI. | 6 |
| RNF-SEG-15 | El tablero de monitoreo y la documentación de producción deberán protegerse con credenciales, siempre sobre HTTPS. | Prueba de acceso sin credenciales. | 21 |

#### 3.6.4 Privacidad (Ley 8968)

| ID | Requisito | Verificación | Clase |
|---|---|---|---|
| RNF-PRI-01 | Datos mínimos: del cliente solo se guardan nombre, correo y teléfono opcional. Nunca cédula ni dirección. | Revisión del esquema. | 13 |
| RNF-PRI-02 | Consentimiento informado: la aceptación de términos es obligatoria y queda registrada con su fecha. | Prueba de registro sin aceptación. | 13, 14 |
| RNF-PRI-03 | Derechos de acceso, rectificación y supresión disponibles desde la cuenta de la persona (RF-CUE-07 a RF-CUE-09). | Pruebas de funcionalidad. | 13 |
| RNF-PRI-04 | Ningún log, reporte ni respuesta a otra persona deberá incluir datos personales de un cliente, salvo su nombre en los pedidos de la soda donde pidió. | Revisión de logs y Resources. | 13, 21 |
| RNF-PRI-05 | Durante el curso, el sistema deberá operar solo con datos ficticios. | Revisión de los seeders y de la base desplegada. | 7 |

#### 3.6.5 Mantenibilidad y calidad

| ID | Requisito | Métrica o verificación | Clase |
|---|---|---|---|
| RNF-MAN-01 | Estilo de código. | Laravel Pint sin cambios pendientes. | 6 |
| RNF-MAN-02 | Análisis estático. | Larastan en nivel 6 o superior, sin errores. | 6 |
| RNF-MAN-03 | Pruebas. | PHPUnit en verde en cada pull request. Toda funcionalidad nueva agrega al menos una prueba. | 5, 6 |
| RNF-MAN-04 | Caminos críticos. | Hay pruebas del aislamiento entre sodas, la concurrencia de la última porción, el saldo de puntos, el cobro y el webhook. | 8 a 17 |
| RNF-MAN-05 | Motor de pruebas. | Las pruebas corren contra PostgreSQL, igual que producción, también en la CI. | 10 |
| RNF-MAN-06 | Aislamiento de las pruebas. | Ninguna prueba llama a un servicio externo real: todas usan dobles (`Http::fake`, `Queue::fake`). | 11 |
| RNF-MAN-07 | Flujo de trabajo. | Rama por clase, pull request revisado, ruleset en `main` que exige la CI en verde y etiqueta al cerrar cada clase. | 6 |
| RNF-MAN-08 | Uso de IA en el desarrollo. | El README declara qué partes se generaron con asistencia de IA y qué se verificó. | 6 |

#### 3.6.6 Portabilidad

| ID | Requisito | Métrica o verificación | Clase |
|---|---|---|---|
| RNF-POR-01 | Configuración. | Toda la configuración que cambia entre ambientes vive en variables de entorno. | 6 |
| RNF-POR-02 | Instalación reproducible. | Una persona nueva levanta el sistema en local siguiendo solo el README, en 30 minutos o menos. | 6 |
| RNF-POR-03 | Contenedores. | El sistema completo (API, Nginx, PostgreSQL, Redis, worker, scheduler y Reverb) se levanta con `docker compose up`. | 23 |
| RNF-POR-04 | Recursos mínimos. | El sistema opera en un servidor virtual de 2 vCPU y 2 GB de RAM. | 23 |

#### 3.6.7 Usabilidad y accesibilidad

| ID | Requisito | Métrica o verificación | Clase |
|---|---|---|---|
| RNF-USA-01 | Diseño responsivo. | El cliente web se usa sin desplazamiento horizontal desde 360 px de ancho. | 17 |
| RNF-USA-02 | Pedido rápido. | Un cliente con cuenta completa un pedido de un plato, con el pago, en 5 pasos o menos. | 17, 18 |
| RNF-USA-03 | Cocina. | Avanzar un pedido requiere un solo toque. Los botones miden al menos 44 × 44 px. | 21 |
| RNF-USA-04 | Accesibilidad. | WCAG 2.2 nivel AA en contraste, navegación con teclado y etiquetas de formulario. | 17 |
| RNF-USA-05 | Idioma. | Toda la interfaz y todos los mensajes de error, incluidos los de validación, en español. | 5, 17 |

### 3.7 Otros requisitos

| ID | Requisito | Clase |
|---|---|---|
| OR-01 | El repositorio deberá documentar en el README la instalación, los comandos de calidad, la dirección pública y la forma de trabajo. | 6 |
| OR-02 | Cada técnica nueva deberá quedar en una etiqueta `clase-NN` del repositorio, para que los equipos comparen lo que agregó cada clase. | Todas |
| OR-03 | Durante el curso, el sistema no deberá publicarse como servicio comercial. | Todas |

---

## Apéndice A. Acta de levantamiento de requisitos (simulación)

| Campo | Detalle |
|---|---|
| Fecha | Viernes 25 de setiembre de 2026 |
| Lugar | Soda La Esquina, Ciudad Quesada, San Carlos (ficticia) |
| Participantes | Lucía Araya Quesada, dueña; Mario Solís, cocinero; Bryan Chaves, líder técnico. Todas las personas de la soda son ficticias. |
| Técnicas | Entrevista semiestructurada con la dueña (9:00 a 10:30), observación del mostrador y la cocina en la hora pico (11:30 a 13:00) y revisión del cuaderno de pedidos. |

### A.1 Cómo trabaja hoy la soda

- Cada mañana doña Lucía escribe el menú en una pizarra junto a la puerta y le toma una foto para el estado de WhatsApp.
- Los pedidos entran por el mostrador, por llamada y por WhatsApp. Se anotan en un cuaderno y en papelitos que van a la cocina.
- Durante la observación entraron 41 pedidos entre las 11:30 y las 13:00: 17 por WhatsApp, 9 por teléfono y 15 en el mostrador.
- Hubo dos ventas de un casado de pescado que ya se había acabado; hubo que llamar a los clientes para disculparse.
- Un encargo de 3 casados quedó sin recoger y se perdió.
- De 14:00 a 15:00 la soda queda casi vacía.

### A.2 Necesidades identificadas

| ID | Necesidad | Lo que dijo la clienta |
|---|---|---|
| N-01 | Que los clientes vean qué hay de almuerzo sin llamar. | "La mitad del teléfono es gente preguntando qué hay hoy." |
| N-02 | No vender lo que ya se acabó. | "Lo peor es llamar a alguien para decirle que ya no hay casado de pescado cuando ya lo pagó." |
| N-03 | Que los encargos se paguen antes, para no perder comida. | "Si lo pagan antes, lo vienen a traer." |
| N-04 | Que el precio cobrado quede registrado. | "Subí el casado y un cliente me alegó que ayer se lo cobré más barato." |
| N-05 | Que la cocina sepa qué sigue, sin papelitos. | Mario: "Los papelitos se mojan y se revuelven." |
| N-06 | Premiar a los clientes que vienen todos los días. | "Tengo una tarjetita de cartón con sellos, pero la pierden." |
| N-07 | Atraer clientes en la hora más floja. | "De dos a tres no entra nadie. Podría bajar el precio a esa hora." |
| N-08 | Que su hermano use el sistema en su soda sin ver los datos de ella. | "Mi hermano tiene una soda en Florencia y también lo quiere, pero cada uno con lo suyo." |
| N-09 | Que la cocina no cambie precios ni vea las ventas. | "Mario tiene que ver los pedidos, pero no cuánto vendí." |
| N-10 | Saber a qué hora vende más. | "Quiero saber a qué hora me conviene tener más comida lista." |
| N-11 | No duplicar el trabajo de escribir el menú. | "Ya escribo la pizarra todas las mañanas. No quiero escribir lo mismo otra vez en el teléfono." |
| N-12 | Avisar al cliente cuando el pedido está listo. | "Que les llegue un mensaje cuando ya está, para que no esperen aquí de pie." |
| N-13 | Avisar si vuelve a haber un plato agotado. | "Cuando se acaba el chifrijo me preguntan si voy a hacer más." |
| N-14 | Organizar los encargos grandes con fecha. | "En diciembre me encargan tamales y lo anoto en un almanaque." |
| N-15 | Recibir propinas para la persona que atiende. | "La gente quiere dejarle algo a la muchacha, pero ya casi nadie anda efectivo." |
| N-16 | Saber qué opinan los clientes. | "Me gustaría saber qué les gusta y qué no, sin leer cien comentarios." |
| N-17 | Aparecer cuando alguien le pregunta a un asistente dónde almorzar. | "Mi nieto dice que la gente ya le pregunta al teléfono dónde comer." |
| N-18 | Que el sistema no falle al mediodía ni pierda datos. | "Si se cae a las doce, me mata." |
| N-19 | No guardar datos de más de los clientes. | "Leí que hay una ley de datos. No quiero problemas por guardar cosas que no ocupo." |

### A.3 Solicitudes que quedaron fuera del alcance

| Solicitud | Decisión | Motivo |
|---|---|---|
| Cobrar por SINPE Móvil. | Pospuesta (POS-01). | Requiere un proveedor local; en el curso se trabaja con ONVO en la Investigación 1. |
| Factura electrónica. | Pospuesta (POS-02). | Complejidad regulatoria fuera del alcance. |
| Entregas a domicilio. | Pospuesta (POS-04). | Otro modelo de negocio. |
| Llevar el inventario de ingredientes. | Pospuesta (POS-07). | La soda controla porciones, no ingredientes. |

### A.4 Acuerdos

1. La clienta prioriza, en este orden: menú visible, no vender de más, cobro por adelantado y cocina ordenada. Esos son los requisitos esenciales.
2. Los puntos, la lista de espera, la hora feliz, las propinas, las reseñas, el calendario y el asistente de IA se construyen como extras, después del núcleo.
3. La clienta revisa el borrador el 28/09 y aprueba la versión 1.0 el 29/09.

## Apéndice B. Casos de uso core

### B.1 Realizar un pedido

| Campo | Contenido |
|---|---|
| Actor | Cliente. |
| Precondiciones | Tiene una sesión válida. La soda existe. |
| Disparador | El cliente confirma su carrito. |
| Flujo principal | 1. El cliente envía los platos y las cantidades. 2. El sistema revisa que la soda esté abierta. 3. Revisa que cada plato sea de esa soda y esté disponible. 4. Bloquea las porciones y las descuenta. 5. Congela el precio vigente de cada plato. 6. Calcula el total. 7. Guarda el pedido como pendiente. 8. Avisa a la cocina y programa la cancelación automática. |
| Flujos alternos | 0a. Sin sesión: 401. 1a. Datos con forma incorrecta: 422. 2a. Soda cerrada: 409. 3a. Plato de otra soda: 422. 3b. Plato no disponible: 409. 4a. Porciones agotadas: 409. |
| Postcondición | Existe un pedido pendiente con su total, y las porciones quedaron descontadas. |
| Requisitos | RF-PED-01 a RF-PED-10, RF-AUT-01, RF-VIV-01. |

### B.2 Cancelar mi pedido

| Campo | Contenido |
|---|---|
| Actor | Cliente. |
| Precondiciones | El pedido es suyo y está pendiente o confirmado. |
| Flujo principal | 1. El cliente pide cancelar. 2. El sistema cambia el estado a cancelado. 3. Devuelve las porciones en la misma transacción. 4. Ofrece las porciones liberadas a la lista de espera. 5. Avisa a la cocina. |
| Flujos alternos | Pedido ajeno: 404. Pedido listo o entregado: 409. |
| Requisitos | RF-PED-12, RF-PED-13, RF-ESP-02. |

### B.3 Avanzar un pedido

| Campo | Contenido |
|---|---|
| Actor | Cocina. |
| Precondiciones | El pedido es de su soda. |
| Flujo principal | 1. La cocina elige el siguiente estado. 2. El sistema valida la transición. 3. Registra la hora del cambio. 4. Publica el evento en tiempo real. 5. Si el pedido quedó listo, avisa al cliente. 6. Si quedó entregado, acredita los puntos en cola. |
| Flujos alternos | Token sin ability de cocina: 403. Pedido de otra soda: 404. Transición inválida: 409. |
| Requisitos | RF-COC-02, RF-COC-04, RF-AVI-01, RF-LEA-01, RF-VIV-01. |

### B.4 Pagar mi pedido

| Campo | Contenido |
|---|---|
| Actores | Cliente, con Stripe como actor de apoyo. |
| Precondiciones | El pedido es suyo, no está cancelado y no está pagado. |
| Flujo principal | 1. El cliente pide pagar, con propina opcional. 2. El sistema calcula el monto desde el pedido guardado. 3. Crea el cobro con una clave de idempotencia. 4. Registra el pago como pendiente. 5. El proveedor confirma por webhook firmado. 6. El sistema marca el pedido como pagado en una transacción. |
| Flujos alternos | Ya pagado: 409. Rechazado: el pago queda rechazado y el cliente puede reintentar. Proveedor sin respuesta: 503 y pago pendiente. |
| Requisitos | RF-PAG-01 a RF-PAG-08, RF-EXT-04. |

### B.5 Estados del pedido

```
pendiente ----> confirmado ----> listo ----> entregado
    |               |
    +-------+-------+
            |
            v
        cancelado
```

## Apéndice C. Matriz de trazabilidad

### C.1 De las necesidades a los requisitos

| Necesidad | Requisitos que la atienden |
|---|---|
| N-01 Menú visible | RF-MEN-01, RF-MEN-04 a RF-MEN-07, IU-01 |
| N-02 No vender de más | RF-MEN-09, RF-PED-02, RF-PED-04 a RF-PED-07, RF-PED-13, RF-AUT-03 |
| N-03 Cobro por adelantado | RF-PAG-01 a RF-PAG-05, RF-PAG-09, RF-PED-12, RF-AUT-01 |
| N-04 Precio registrado | RF-MEN-02, RF-PED-08, RF-PED-09, RF-PED-11, RF-COC-04 |
| N-05 Cocina ordenada | RF-COC-01 a RF-COC-03, RF-VIV-01, RF-VIV-03, IU-03 |
| N-06 Premiar clientes | RF-LEA-01, RF-LEA-02, RF-LEA-04, RF-LEA-05 |
| N-07 Hora floja | RF-EXT-01, RF-EXT-03 |
| N-08 Varias sodas | RF-CUE-05, RF-SOD-01 a RF-SOD-04, RF-VIV-02 |
| N-09 Permisos de cocina | RF-CUE-02, RF-CUE-04, RF-CUE-06, RF-IA-06, RNF-SEG-02 |
| N-10 Horas de venta | RF-REP-01, RF-REP-02 |
| N-11 Menú desde la pizarra | RF-IA-01 a RF-IA-04 |
| N-12 Aviso de pedido listo | RF-AVI-01, RF-AVI-03, RF-VIV-04 |
| N-13 Plato agotado | RF-ESP-01 a RF-ESP-03 |
| N-14 Encargos con fecha | RF-EXT-07 |
| N-15 Propinas | RF-EXT-04, RF-EXT-05 |
| N-16 Opinión de clientes | RF-EXT-08, RF-EXT-09 |
| N-17 Asistentes de IA | RF-EXT-10 |
| N-18 Que no falle | RF-OPE-01, RF-OPE-02, RNF-DIS-01 a RNF-DIS-07 |
| N-19 Datos mínimos | RF-CUE-01, RF-CUE-07 a RF-CUE-09, RNF-PRI-01 a RNF-PRI-05 |

### C.2 Del núcleo del proyecto integrador a SodaYa

Muestra en qué parte de SodaYa se enseña cada capacidad obligatoria del proyecto integrador.

| Núcleo del proyecto integrador | Requisitos de SodaYa | Clase |
|---|---|---|
| 1. Catálogo, horarios y disponibilidad | RF-MEN-01 a RF-MEN-09 | 4, 7 |
| 2. Reserva con control de concurrencia | RF-PED-05 a RF-PED-07, RNF-FIA-02 | 10 |
| 3. Multi-inquilino | RF-SOD-01 a RF-SOD-05 | 8 |
| 4. Tres roles, por rol y por propiedad | RF-CUE-04, RNF-SEG-02, RNF-SEG-03 | 14, 15 |
| 5. Cobro confirmado por webhook | RF-PAG-01 a RF-PAG-08, RF-AVI-02 | 11, 17 |
| 6. Bonos que nunca quedan negativos | RF-LEA-01 a RF-LEA-05 | 10 |
| 7. Lista de espera con tiempo límite | RF-ESP-01 a RF-ESP-04 | 19 |
| 8. Recordatorios y expiración en cola | RF-AUT-01 a RF-AUT-05, RF-AVI-01 | 10 |
| 9. Agenda en vivo | RF-VIV-01 a RF-VIV-03 | 21 |
| 10. IA con datos estructurados | RF-IA-01 a RF-IA-06 | 18 |
| 11. Despliegue, CI, salud y monitoreo | RF-OPE-01 a RF-OPE-03, RNF-DIS-01 a RNF-DIS-08, RNF-MAN-01 a RNF-MAN-07 | 6, 21, 23, 24 |

### C.3 Requisitos por evaluación del curso

Cuenta solo lo construido en las clases anteriores a la fecha de cada evaluación.

| Evaluación | Fecha | Lo que SodaYa ya tiene construido |
|---|---|---|
| Prueba corta 1 | 30/09 | Menú público, errores RFC 9457, versionado `/api/v1`, contrato con Scramble y primeras pruebas (RF-MEN-01, RF-OPE-03, RD-01 a RD-03, RNF-MAN-03). |
| Avance 1 | 21/10 | Además: CI y despliegue, categorías y horario, multi-inquilino, estados del pedido, fotos, reporte de ventas, concurrencia, puntos, colas, tareas programadas, cobro por consulta al proveedor, canales de aviso y caché del menú. |
| Investigación 1 | 28/10 | Además: primer vistazo al cliente, hora feliz, defensas contra las fallas OWASP y derechos de la Ley 8968. |
| Prueba corta 2 | 09/11 | Además: tokens completos, propinas, comisiones, policies, límites de tasa, encabezados de seguridad, CORS y Google Calendar. |
| Avance 2 | 18/11 | Además: cliente completo, webhook de pagos, IA de la pizarra, estado del pedido por SSE, lista de espera y WhatsApp. |
| Investigación 2 | 25/11 | Además: reseñas con resumen de IA, pantalla de cocina en tiempo real y observabilidad. |
| Entrega final | 07/12 | Además: servidor MCP, contenedores, despliegue automático, TLS y respaldos. Todos los requisitos esenciales. |

## Apéndice D. Asuntos pendientes

| ID | Asunto | Responsable | Fecha límite |
|---|---|---|---|
| P-01 | Confirmar que Stripe en modo de prueba funciona desde Costa Rica sin activar la cuenta (S-01). | Docente | 19/10 |
| P-02 | Definir el valor de canje de un punto en colones y si el canje se aplica como descuento sobre un pedido. RF-LEA-04 hoy solo descuenta el saldo. | Clienta y docente | 14/10 |
| P-03 | Asignar la clase en que se construye el reembolso automático (RF-PAG-09) o dejarlo como pospuesto. | Docente | 19/10 |
| P-04 | Unificar el disparador de la lista de espera. El plan de clases dice "al cancelarse un pedido" y el manual técnico dice "cuando el dueño repone porciones". Esta ERS acepta ambos (RF-ESP-02). | Docente | 16/11 |
| P-05 | Unificar el alcance del servidor MCP. El plan de clases incluye pedidos pendientes y ventas de hoy, con autenticación por soda; el manual técnico lo limita a datos públicos. Esta ERS adopta la versión de solo datos públicos (RF-EXT-10). | Docente | 25/11 |
| P-06 | Confirmar si el proveedor de IA se consume con su SDK oficial, como hace el manual técnico, o por HTTP directo, como pide la regla general del plan de clases. RD-09 exige HTTP directo solo para pagos y mensajería. | Docente | 11/11 |
| P-07 | Confirmar la hora del reinicio diario de porciones (RF-AUT-03). La clienta abre a las 6:00. | Clienta | 14/10 |
