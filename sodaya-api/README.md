# SodaYa API

API REST de SodaYa en Laravel 13 sobre PostgreSQL 18. Publica el menú del día de cada soda y recibe pedidos para llevar.

## Requisitos

- PHP 8.4 o superior, con la extensión `pdo_pgsql`.
- Composer 2.
- PostgreSQL 18.6.

## Instalación local

```sh
git clone git@github.com:BryanChavesSalas/SodaYa.git
cd SodaYa/sodaya-api
composer install
cp .env.example .env
php artisan key:generate
```

Cree las dos bases de datos, una para trabajar y otra para las pruebas:

```sh
createdb -h 127.0.0.1 -U postgres sodaya
createdb -h 127.0.0.1 -U postgres sodaya_test
```

Ajuste en `.env` las variables `DB_*` si su PostgreSQL usa otro puerto, usuario o contraseña. Después:

```sh
php artisan migrate
php artisan serve
```

La API queda en `http://localhost:8000`, y `http://localhost:8000/up` responde 200 cuando la aplicación arrancó bien.

## Contrato de la API

Todas las rutas viven bajo `/api/v1`. Scramble genera el contrato OpenAPI 3.1 desde el código:

- Documentación interactiva en `http://localhost:8000/docs/api`, solo en el ambiente local.
- Contrato versionado en [`openapi/v1.json`](openapi/v1.json). Después de cambiar la API se exporta de nuevo, y una prueba falla si quedó desactualizado:

```sh
php artisan scramble:export --path=openapi/v1.json
```

Los errores responden `application/problem+json` (RFC 9457) y cada respuesta lleva el encabezado `X-Request-Id`.

## Pruebas

```sh
php artisan test
```

Las pruebas corren contra la base `sodaya_test` de PostgreSQL, el mismo motor de producción. `phpunit.xml` fija esa base; el servidor, el puerto y el usuario salen de `.env`.
