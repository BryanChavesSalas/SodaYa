<?php

namespace App\Http\Problemas;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use SodaYa\Compartido\Domain\ErrorDeDominio;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class RenderizadorDeProblemas
{
    /** Convierte la excepción de una ruta de la API en un problema RFC 9457. */
    public function renderizar(Throwable $e, Request $request): ?Response
    {
        if (! $request->is('api/*') || $e instanceof HttpResponseException) {
            return null;
        }

        return $this->problemaPara($e)->aRespuesta();
    }

    /** Elige el problema de la excepción; solo muestra mensajes escritos para el cliente. */
    private function problemaPara(Throwable $e): Problema
    {
        return match (true) {
            $e instanceof ValidationException => Problema::de(TipoDeProblema::DatosInvalidos, extensiones: ['errors' => $e->errors()]),
            $e instanceof AuthenticationException => Problema::de(TipoDeProblema::NoAutenticado, encabezados: ['WWW-Authenticate' => 'Bearer']),
            $e instanceof ErrorDeDominio => Problema::de(
                TipoDeProblema::tryFrom($e->codigo()) ?? TipoDeProblema::Conflicto,
                $e->getMessage(),
                $e->contexto(),
            ),
            $e instanceof HttpExceptionInterface => $this->deHttp($e),
            default => $this->interno($e),
        };
    }

    /** Traduce un error HTTP con un detalle fijo; sin semántica propia usa about:blank. */
    private function deHttp(HttpExceptionInterface $e): Problema
    {
        $estado = $e->getStatusCode();
        $tipo = TipoDeProblema::paraEstado($estado);

        if ($tipo === null) {
            $texto = Response::$statusTexts[$estado] ?? 'Error';

            return new Problema($estado, 'about:blank', $texto, $texto, encabezados: $e->getHeaders());
        }

        return new Problema($estado, $tipo->uri(), $tipo->titulo(), $tipo->detalle(), encabezados: $e->getHeaders());
    }

    /** Error inesperado: en desarrollo agrega el origen, nunca la traza ni el SQL completo. */
    private function interno(Throwable $e): Problema
    {
        $extensiones = config('app.debug') ? ['debug' => [
            'excepcion' => $e::class,
            'mensaje' => $e->getMessage(),
            'origen' => basename($e->getFile()).':'.$e->getLine(),
        ]] : [];

        return Problema::de(TipoDeProblema::ErrorInterno, extensiones: $extensiones);
    }
}
