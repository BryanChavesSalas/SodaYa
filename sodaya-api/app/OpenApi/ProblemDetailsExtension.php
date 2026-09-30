<?php

namespace App\OpenApi;

use App\Http\Problemas\TipoDeProblema;
use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use SodaYa\Compartido\Domain\ErrorDeDominio;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProblemDetailsExtension extends ExceptionToResponseExtension
{
    /** Atiende las excepciones que la API responde como problem details. */
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType && $this->estado($type) !== null;
    }

    /** Documenta la respuesta application/problem+json con los tipos posibles de su código. */
    public function toResponse(Type $type): ?Response
    {
        if (! $type instanceof ObjectType || ($estado = $this->estado($type)) === null) {
            return null;
        }

        $tipos = array_map(fn (TipoDeProblema $tipo): string => $tipo->value, TipoDeProblema::conEstado($estado));
        $esquema = $this->esquema($estado);

        return Response::make($estado)
            ->setDescription('Problem details (RFC 9457). Valores posibles del final de "type": '.implode(', ', $tipos).'.')
            ->setContent('application/problem+json', Schema::fromType($esquema));
    }

    /** Las excepciones con el mismo código comparten un componente del contrato. */
    public function reference(ObjectType $type): Reference
    {
        return new Reference('responses', 'Problema'.$this->estado($type), $this->components);
    }

    /** Código HTTP con que la API responde la excepción, o null si no la atiende. */
    private function estado(ObjectType $type): ?int
    {
        return match (true) {
            $type->isInstanceOf(ValidationException::class) => 422,
            $type->isInstanceOf(AuthenticationException::class) => 401,
            $type->isInstanceOf(AuthorizationException::class) => 403,
            $type->isInstanceOf(RecordsNotFoundException::class),
            $type->isInstanceOf(NotFoundHttpException::class) => 404,
            $type->isInstanceOf(ErrorDeDominio::class) => $this->estadoDeDominio($type->name),
            default => null,
        };
    }

    /** Código HTTP de una regla de negocio, leído del catálogo con el código del error. */
    private function estadoDeDominio(string $clase): int
    {
        if (! class_exists($clase) || (new ReflectionClass($clase))->isAbstract()) {
            return TipoDeProblema::Conflicto->estado();
        }

        $error = (new ReflectionClass($clase))->newInstanceWithoutConstructor();

        return $error instanceof ErrorDeDominio
            ? (TipoDeProblema::tryFrom($error->codigo()) ?? TipoDeProblema::Conflicto)->estado()
            : TipoDeProblema::Conflicto->estado();
    }

    /** Esquema del problem details; en 422 agrega los errores por campo. */
    private function esquema(int $estado): OpenApi\ObjectType
    {
        $esquema = (new OpenApi\ObjectType)
            ->addProperty('type', (new OpenApi\StringType)->format('uri')->setDescription('Identificador estable del problema; termina en su código.'))
            ->addProperty('title', (new OpenApi\StringType)->setDescription('Resumen legible; no cambia entre ocurrencias.'))
            ->addProperty('status', (new OpenApi\IntegerType)->setDescription('Código HTTP, repetido en el cuerpo.'))
            ->addProperty('detail', (new OpenApi\StringType)->setDescription('Explicación de esta ocurrencia.'))
            ->addProperty('instance', (new OpenApi\StringType)->nullable(true)->setDescription('urn:uuid con el mismo valor del encabezado X-Request-Id.'))
            ->setRequired(['type', 'title', 'status', 'detail']);

        if ($estado === 422) {
            $esquema->addProperty('errors', (new OpenApi\ObjectType)
                ->additionalProperties((new OpenApi\ArrayType)->setItems(new OpenApi\StringType))
                ->setDescription('Mensajes por campo, con la forma que usa Laravel.'));
        }

        return $esquema;
    }
}
