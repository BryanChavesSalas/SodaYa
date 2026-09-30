<?php

use App\Http\Middleware\AsignarIdDeSolicitud;
use App\Http\Problemas\RenderizadorDeProblemas;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use SodaYa\Compartido\Domain\ErrorDeDominio;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(AsignarIdDeSolicitud::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->dontReport([ErrorDeDominio::class]);

        $exceptions->render(
            fn (Throwable $e, Request $request) => app(RenderizadorDeProblemas::class)->renderizar($e, $request),
        );
    })->create();
