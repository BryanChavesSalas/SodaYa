# Despliegue en producción

La plataforma todavía no está elegida (issue #16). Esta guía deja listo todo lo que no depende de ella.

## Lo que necesita la plataforma

- PHP 8.4 o superior con la extensión `pdo_pgsql`, y Composer 2.
- PostgreSQL 18 gestionado.
- HTTPS con un certificado válido en la dirección pública.
- Variables de entorno configuradas en la plataforma, nunca en el repositorio.

## Variables de entorno

| Variable | Valor en producción |
|---|---|
| `APP_NAME` | `SodaYa` |
| `APP_ENV` | `production` |
| `APP_KEY` | La salida de `php artisan key:generate --show`, guardada solo en la plataforma. |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://` más la dirección pública. |
| `APP_LOCALE` | `es` |
| `PROBLEMAS_URI` | El mismo valor en todos los ambientes: identifica los tipos de error, no el servidor. |
| `API_VERSION` | `1.0.0` |
| `LOG_CHANNEL` | `stderr`, para que la plataforma recoja los logs. |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | La cadena de conexión de la base gestionada, o las variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. |

## Pasos de cada despliegue

```sh
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize
```

`--no-dev` deja fuera Pint, Larastan y `laravel-lang`. Los archivos de idioma ya están versionados en `lang/`, así que los mensajes siguen en español.

## Verificación después de desplegar

```sh
curl -i https://<dirección-pública>/up
curl -i https://<dirección-pública>/api/v1/sodas/999/platos
```

- `/up` responde 200.
- La consulta de una soda inexistente responde 404 `application/problem+json`, con `X-Request-Id` y sin bloque `debug`: eso confirma `APP_DEBUG=false`.
- `/docs/api` responde 403: Scramble publica la documentación solo en el ambiente local.
- La base de datos contiene solo datos ficticios.

## Pendiente

- Elegir la plataforma y crear la cuenta.
- Publicar la dirección pública en el README y cerrar el issue #16.
