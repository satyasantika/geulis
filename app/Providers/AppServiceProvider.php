<?php

namespace App\Providers;

use App\Http\Responses\KeluarPanelResponse;
use App\Services\Differentiation\DifferentiationConfig;
use App\Services\Differentiation\DifferentiationEngine;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Satu mesin untuk seluruh aplikasi, parameternya dari config/geulis.php.
        $this->app->singleton(DifferentiationEngine::class, fn (): DifferentiationEngine => new DifferentiationEngine(DifferentiationConfig::fromConfig()));
        $this->app->bind(LogoutResponseContract::class, KeluarPanelResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
