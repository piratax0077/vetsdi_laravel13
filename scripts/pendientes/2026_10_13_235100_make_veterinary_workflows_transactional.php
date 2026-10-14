<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, File};

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') return;
        $modelos = ['Mascota','MascotaGenealogia','FichaAtencion','HoraMedica','FichaVeterinariaGeneral',
            'FichaCirugiaGeneralAdulto','ControlPostQuirurgico','DetalleReceta','ExamenPPF',
            'PresupuestosDental','PagosPresupuestoDental','PresupuestoMascota','User','Paciente',
            'Profesional','Asistente','Instituciones','AdminInstServ','Direccion','ContactoEmergencia',
            'PacienteContactoEmergencia','ConConsentimientosPcte','LogUsersDevices'];
        $tablas = ['mascotas_lugares_atencion','odontogramas_mascotas','odontogramas_fichas_mascotas'];
        foreach ($modelos as $modelo) {
            $clase = 'App\\Models\\'.$modelo;
            if (class_exists($clase)) $tablas[] = (new $clase)->getTable();
        }
        foreach (array_unique($tablas) as $tabla) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) throw new RuntimeException('Nombre de tabla inválido.');
            $meta = DB::selectOne('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$tabla]);
            if (!$meta || strtoupper($meta->ENGINE) === 'INNODB') continue;
            // Export schema and exact values before changing only the storage engine.
            $filas = DB::table($tabla)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
            $huella = hash('sha256', json_encode($filas, JSON_THROW_ON_ERROR));
            $directorio = storage_path('app/auditoria/motores_resp');
            File::ensureDirectoryExists($directorio);
            $archivo = $directorio.'/'.$tabla.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.sql.gz';
            $salida = gzopen($archivo, 'wb9');
            if (!$salida) throw new RuntimeException('No se pudo crear el respaldo.');
            try {
                $ddl = (array) DB::selectOne('SHOW CREATE TABLE `'.$tabla.'`');
                $sql = '-- Respaldo previo a la conversión InnoDB; restaurar manualmente en una base separada.'.PHP_EOL
                    .array_values($ddl)[1].';'.PHP_EOL;
                if (gzwrite($salida, $sql) !== strlen($sql)) throw new RuntimeException('Respaldo incompleto.');
                foreach ($filas as $fila) {
                    $columnas = implode(',', array_map(fn ($c) => '`'.$c.'`', array_keys($fila)));
                    $valores = implode(',', array_map(fn ($v) => $v === null ? 'NULL' : DB::getPdo()->quote((string) $v), array_values($fila)));
                    $sql = 'INSERT INTO `'.$tabla.'` ('.$columnas.') VALUES ('.$valores.');'.PHP_EOL;
                    if (gzwrite($salida, $sql) !== strlen($sql)) throw new RuntimeException('Respaldo incompleto.');
                }
            } finally { gzclose($salida); }
            DB::statement('ALTER TABLE `'.$tabla.'` ENGINE=InnoDB, ROW_FORMAT=DYNAMIC');
            $actuales = DB::table($tabla)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
            if ($huella !== hash('sha256', json_encode($actuales, JSON_THROW_ON_ERROR))) {
                throw new RuntimeException('Los datos cambiaron durante la conversión de '.$tabla.'. Revise el respaldo.');
            }
            File::put($archivo.'.json', json_encode(['tabla'=>$tabla,'registros'=>count($filas),'sha256_datos'=>$huella,'sha256_respaldo'=>hash_file('sha256',$archivo),'motor_anterior'=>$meta->ENGINE,'motor_actual'=>'InnoDB'], JSON_PRETTY_PRINT));
            echo $tabla.': '.count($filas).' registros, respaldo y huella verificados.'.PHP_EOL;
        }
    }

    public function down(): void
    {
        // Do not remove data or disable transactions when rolling application code back.
    }
};
