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

    // El catálogo humano se conserva en la base histórica, pero no se ofrece
    // en los formularios veterinarios como si fuera un convenio para mascotas.
    \Illuminate\Support\Facades\View::composer('*', function ($view) {
        $datos = $view->getData();
        foreach (['prevision', 'previsiones', 'lista_prevision'] as $clave) {
            if (isset($datos[$clave]) && (is_array($datos[$clave]) || $datos[$clave] instanceof \Illuminate\Support\Collection)) {
                $view->with($clave, \App\Support\PrevisionesVeterinarias::paraSelect($datos[$clave]));
            }
        }
    });
    \Illuminate\Support\Facades\View::composer('app.profesional.agenda', function ($view) {
        $convenios = collect();
        $profesional = $view->getData()['profesional'] ?? null;
        if ($profesional && \Illuminate\Support\Facades\Schema::hasTable('convenios_veterinarios')) {
            $convenios = \App\Models\ConvenioVeterinario::where('id_profesional', $profesional->id)
                ->where('activo', true)->orderBy('nombre')->get();
        }
        $view->with('conveniosVeterinarios', $convenios);
    });
}

}
