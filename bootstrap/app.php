<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance']);
        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Manejar códigos de error específicos (403, 404, 500)
        $exceptions->render(function (HttpException $e) {
            $status = $e->getStatusCode();
            if (in_array($status, [403, 404, 500])) {
                return Inertia::render("Errors/{$status}")
                    ->toResponse(request())
                    ->setStatusCode($status);
            }
            return null;
        });
        
        // Capturar cualquier otra excepción y mostrar la página de error 500
        $exceptions->render(function (\Throwable $e) {
            return Inertia::render("Errors/500")
                ->toResponse(request())
                ->setStatusCode(500);
        });
    })->create();