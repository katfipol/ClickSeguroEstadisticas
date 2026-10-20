<?php

namespace App\Providers;

use App\Contracts\EscaneoSeguridadInterface;
use App\Services\VirusTotalService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EscaneoSeguridadInterface::class, VirusTotalService::class);
    }

    public function boot(): void
    {
        //
    }
}