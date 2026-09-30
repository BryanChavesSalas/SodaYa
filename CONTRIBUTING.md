# Flujo de trabajo

## Ramas

Cada clase se trabaja en una rama `clase-NN` que sale de `main`:

```sh
git switch main && git pull
git switch -c clase-07
```

Nadie sube directo a `main`: todo entra por pull request.

## Commits

[Conventional Commits](https://www.conventionalcommits.org/es/v1.0.0/), atómicos: un commit hace una sola cosa y deja las pruebas en verde.

```text
feat(api): valida la forma del pedido con un Form Request
```

| Tipo | Para |
|---|---|
| `feat` | Una funcionalidad nueva. |
| `fix` | La corrección de un defecto. |
| `test` | Pruebas que no cambian el comportamiento. |
| `refactor` | Un cambio de estructura sin cambiar el comportamiento. |
| `docs` | Documentación. |
| `build` | Dependencias, Composer y configuración del proyecto. |
| `ci` | Integración continua. |
| `chore` | Mantenimiento del repositorio. |

El tipo va en inglés y la descripción en español, en imperativo. Ningún commit ni pull request lleva firmas, trailers ni marcas de atribución.

## Pull requests

- Un pull request por clase, de `clase-NN` a `main`, con la plantilla del repositorio.
- Enlaza cada issue que resuelve con `Closes #n`, uno por línea.
- Necesita la integración continua en verde, una aprobación y las conversaciones resueltas.
- Se integra con **rebase**: los commits atómicos llegan tal cual a `main`, con historial lineal. La rama se borra sola.

## Etiquetas

Al integrar la clase se marca `main` con la etiqueta `clase-NN`, para comparar lo que agregó cada clase:

```sh
git switch main && git pull
git branch -D clase-07
git tag -a clase-07 -m "Clase 7: persistencia y mapeo de datos"
git push origin refs/tags/clase-07
```

La etiqueta se llama igual que la rama; por eso se borra la rama local y se sube la etiqueta con su referencia completa.

## Tablero

Cada issue avanza en el [Project](https://github.com/users/BryanChavesSalas/projects/9) por las columnas Backlog, Por hacer, En progreso, En revisión y Hecho.

| Momento | Columna |
|---|---|
| Empieza la clase del issue | En progreso, con responsable asignado |
| Se abre el pull request | En revisión |
| Se integra a `main` | Hecho |

## Protección de `main`

El ruleset `proteger-main` exige pull request con una aprobación, historial lineal y conversaciones resueltas, y prohíbe borrar la rama o forzar un push. Mientras no haya un segundo revisor, el administrador puede integrar sin aprobación, pero solo dentro de un pull request.

**CODEOWNERS y el dueño único.** GitHub no deja que el autor apruebe su propio pull request. Si una carpeta tiene un solo responsable y la revisión de responsables se vuelve obligatoria, nadie puede integrar los cambios de esa persona. Cada carpeta necesita al menos dos responsables antes de activar esa regla.
