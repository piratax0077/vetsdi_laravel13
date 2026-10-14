<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Throwable;

class AuditarSistema extends Command
{
    protected $signature = 'sistema:auditar {--json : Emitir el informe en JSON}';
    protected $description = 'Revisar controladores de todas las rutas sin ejecutar acciones ni modificar datos';

    public function handle(): int
    {
        $fallas = [];
        $total = 0;
        foreach (Route::getRoutes() as $ruta) {
            $total++;
            $accion = $ruta->getActionName();
            if ($accion === 'Closure') continue;
            [$clase, $metodo] = array_pad(explode('@', $accion, 2), 2, '__invoke');
            try {
                if (!class_exists($clase) || !method_exists($clase, $metodo)) {
                    $motivo = 'Controlador o método inexistente';
                } elseif (!(new ReflectionMethod($clase, $metodo))->isPublic()) {
                    $motivo = 'Método no público';
                } else {
                    continue;
                }
            } catch (Throwable $e) {
                $motivo = 'No se puede cargar el controlador: '.$e->getMessage();
            }
            $fallas[] = ['nombre' => $ruta->getName(), 'uri' => $ruta->uri(), 'accion' => $accion, 'motivo' => $motivo];
        }
        if ($this->option('json')) {
            $this->line(json_encode(['rutas' => $total, 'fallas' => $fallas], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("Rutas revisadas: {$total}. Fallas: ".count($fallas));
            foreach ($fallas as $falla) $this->error(($falla['nombre'] ?? $falla['uri']).': '.$falla['accion'].' — '.$falla['motivo']);
        }
        return $fallas ? self::FAILURE : self::SUCCESS;
    }
}
