<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $modelName = class_basename($e->getModel());
                return response()->json([
                    'error' => "{$modelName} Not Found",
                    'message' => "The requested {$modelName} does not exist or has been deleted."
                ], 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
             if ($request->is('api/*') || $request->expectsJson()) {
                 if ($e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                     $modelName = class_basename($e->getPrevious()->getModel());
                     return response()->json([
                        'error' => "{$modelName} Not Found",
                        'message' => "The requested {$modelName} does not exist or has been deleted."
                    ], 404);
                 }
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'The requested resource could not be found.'
                ], 404);
            }
        });

        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
    })->create();
