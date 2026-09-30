<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AsignarIdDeSolicitud
{
    public const string ENCABEZADO = 'X-Request-Id';

    /**
     * Identifica la solicitud en la respuesta y en los logs; solo acepta un UUID del cliente.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->headers->get(self::ENCABEZADO);

        if (! is_string($id) || ! Str::isUuid($id)) {
            $id = (string) Str::uuid7();
        }

        Context::add('request_id', $id);

        $response = $next($request);
        $response->headers->set(self::ENCABEZADO, $id);

        return $response;
    }
}
