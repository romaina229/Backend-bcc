<?php
// app/Http/Kernel.php
namespace App\Http;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
   protected $middleware = [
    // ... autres middlewares
    // \App\Http\Middleware\CorsMiddleware::class,
  ];
    protected $middlewareGroups = [
        'web' => [
            // ... autres middlewares web
        ],
        'api' => [
            // ... autres middlewares api
          //  \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
}
