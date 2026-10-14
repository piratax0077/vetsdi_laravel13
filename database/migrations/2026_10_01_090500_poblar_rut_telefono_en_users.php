<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sube a users el RUT y el telefono que hoy viven en cada tabla de perfil, para
 * que las cuentas antiguas tambien puedan ingresar con RUT.
 *
 * Solo se copia cuando el RUT es inequivoco: si el mismo RUT aparece en mas de
 * un usuario se deja en blanco y queda para revisar a mano, porque el indice
 * unico no admite duplicados.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cada tabla de perfil nombra distinto su telefono principal.
        $tablas = [
            'pacientes' => 'telefono_uno',
            'profesionales' => 'telefono_uno',
            'asistentes' => 'telefono_uno',
            'instituciones' => 'telefono',
        ];

        $candidatos = [];

        foreach ($tablas as $tabla => $columnaTelefono) {
            $registros = DB::table($tabla)
                ->select('id_usuario', 'rut', $columnaTelefono.' as telefono')
                ->whereNotNull('id_usuario')
                ->whereNotNull('rut')
                ->where('rut', '<>', '')
                ->get();

            foreach ($registros as $registro) {
                $rut = $this->normalizarRut($registro->rut);
                if ($rut === null) {
                    continue;
                }

                $candidatos[$rut][$registro->id_usuario] = $registro->telefono;
            }
        }

        foreach ($candidatos as $rut => $usuarios) {
            // El mismo RUT apuntando a dos cuentas distintas es justamente el caso
            // que este modelo quiere evitar: se omite para no romper el indice unico.
            if (count($usuarios) !== 1) {
                continue;
            }

            $idUsuario = array_key_first($usuarios);
            $telefono = $usuarios[$idUsuario];

            $valores = ['rut' => $rut];
            if (! empty($telefono)) {
                $valores['telefono'] = $telefono;
            }

            DB::table('users')
                ->where('id', $idUsuario)
                ->whereNull('rut')
                ->update($valores);
        }
    }

    public function down(): void
    {
        DB::table('users')->update(['rut' => null, 'telefono' => null]);
    }

    /** Deja el RUT como 12345678-K: sin puntos, con guion y en mayuscula. */
    private function normalizarRut(?string $rut): ?string
    {
        $limpio = strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));

        if (strlen($limpio) < 7 || strlen($limpio) > 9) {
            return null;
        }

        return substr($limpio, 0, -1).'-'.substr($limpio, -1);
    }
};
