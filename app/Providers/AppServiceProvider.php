<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */


public function boot(): void
{
    // Esto crea un puente temporal para que Laravel 13 entienda las etiquetas viejas si quedó alguna suelta
    Blade::component('label', 'jet-label');
    Blade::component('button', 'jet-button');
    Blade::component('input', 'jet-input');
    Blade::component('checkbox', 'jet-checkbox');
}

}
