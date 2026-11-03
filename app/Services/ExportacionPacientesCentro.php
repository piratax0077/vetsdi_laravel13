<?php
namespace App\Services;

use App\Models\Instituciones;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\{DB,Schema};
use RuntimeException;
use ZipArchive;

class ExportacionPacientesCentro
{
    private function disponible(string $tabla,array $columnas): bool
    {
        return Schema::hasTable($tabla) && !array_diff($columnas,Schema::getColumnListing($tabla));
    }

    public function consultas(Instituciones $centro): array
    {
        $lugares=collect(preg_split('/[,;]/',(string)$centro->id_lugar_atencion))->map(fn($id)=>(int)$id)->filter();
        if($this->disponible('sucursal',['id_institucion','id_lugar_atencion'])) $lugares=$lugares->merge(DB::table('sucursal')->where('id_institucion',$centro->id)->pluck('id_lugar_atencion'));
        $lugares=$lugares->unique()->values()->all();
        $consultas=[];
        foreach(['horas_medicas','fichas_atenciones'] as $tabla) {
            if(!$this->disponible($tabla,['id','id_lugar_atencion','id_mascota'])) continue;
            $q=DB::table($tabla)->whereIn('id_lugar_atencion',$lugares)->whereNotNull('id_mascota');
            if(Schema::hasColumn($tabla,'id_institucion')) $q->where(function($q) use($centro){$q->where('id_institucion',$centro->id)->orWhereNull('id_institucion')->orWhere('id_institucion',0);});
            $consultas[$tabla]=$q;
        }
        foreach(['vet_reservas_servicios','vet_internaciones'] as $tabla) {
            if($this->disponible($tabla,['id','id_institucion','id_mascota'])) $consultas[$tabla]=DB::table($tabla)->where('id_institucion',$centro->id);
        }
        $fuentes=$consultas;
        if($this->disponible('vet_pacientes_establecimiento',['id_institucion','id_mascota'])) $fuentes['vinculos']=DB::table('vet_pacientes_establecimiento')->where('id_institucion',$centro->id);
        $mascotas=DB::table('mascotas')->where(function($q) use($fuentes){
            $q->whereRaw('1 = 0');
            foreach($fuentes as $fuente) $q->orWhereIn('id',(clone $fuente)->reorder()->select('id_mascota'));
        });
        $campos=array_values(array_intersect(['id','nombre','especie','raza','sexo','fecha_nacimiento','fecha_nac','color','microchip','numero_microchip','esterilizado','peso','id_responsable','created_at','updated_at'],Schema::getColumnListing('mascotas')));
        $consultas=['pacientes_mascotas'=>(clone $mascotas)->select($campos)]+$consultas;
        if($this->disponible('pacientes',['id']) && Schema::hasColumn('mascotas','id_responsable')) {
            $campos=array_values(array_intersect(['id','rut','nombre','apellido_uno','apellido_dos','apellido_paterno','apellido_materno','email','telefono_uno','telefono_dos','telefono','created_at'],Schema::getColumnListing('pacientes')));
            $consultas['tutores']=DB::table('pacientes')->whereIn('id',(clone $mascotas)->select('id_responsable'))->select($campos);
        }
        if(isset($consultas['vet_internaciones'])) {
            foreach(['vet_evoluciones','vet_controles_hospitalarios'] as $tabla) if($this->disponible($tabla,['id','internacion_id'])) $consultas[$tabla]=DB::table($tabla)->whereIn('internacion_id',(clone $consultas['vet_internaciones'])->select('id'));
        }
        return $consultas;
    }

    public static function celda(mixed $valor): string
    {
        $texto=(string)($valor ?? '');
        return !is_numeric($texto) && preg_match('/^[\s]*[=+@-]|^[\t\r\n]/u',$texto) ? "'".$texto : $texto;
    }

    public function crear(Instituciones $centro): string
    {
        $consultas = $this->consultas($centro);
        if (!collect($consultas)->contains(fn($consulta)=>(clone $consulta)->exists())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'exportacion' => 'No hay pacientes ni atenciones para descargar en este centro y sus sucursales.',
            ]);
        }
        return $this->crearArchivo($consultas,"Centro: {$centro->nombre} (ID {$centro->id})");
    }

    public function crearArchivo(array $consultas, string $titular): string
    {
        $directorio=storage_path('app/private/exportaciones');
        if(!is_dir($directorio) && !mkdir($directorio,0700,true) && !is_dir($directorio)) throw new RuntimeException('No se pudo preparar la descarga.');
        $archivo=tempnam($directorio,'centro-');
        $temporales=[];
        $zip=new ZipArchive;
        try {
            if($zip->open($archivo,ZipArchive::OVERWRITE)!==true) throw new RuntimeException('No se pudo generar el archivo ZIP.');
            $resumen=[];
            foreach($consultas as $nombre=>$consulta){
                $ruta=tempnam($directorio,'csv-');$temporales[]=$ruta;
                $csv=fopen($ruta,'wb');
                try {
                    fwrite($csv,"\xEF\xBB\xBF");
                    $primero=(clone $consulta)->first();
                    // Las tablas vacías conservan el esquema de la consulta.
                    $columnas=$primero ? array_keys((array)$primero) : ($consulta->columns && $consulta->columns !== ['*'] ? $consulta->columns : Schema::getColumnListing($consulta->from));
                    fputcsv($csv,$columnas,';', '"','');
                    $cantidad=0;
                    foreach((clone $consulta)->lazyById(1000) as $fila){fputcsv($csv,array_map([self::class,'celda'],array_values((array)$fila)),';','"','');$cantidad++;}
                    $resumen[$nombre.'.csv']=$cantidad;
                } finally {fclose($csv);}
                if(!$zip->addFile($ruta,$nombre.'.csv')) throw new RuntimeException('No se pudo agregar un conjunto de datos.');
            }
            $zip->addFromString('LEEME.txt',"Exportación de pacientes y atenciones\n{$titular}\nFecha: ".now()->toIso8601String()."\nFormato: CSV UTF-8, separador punto y coma.\nIncluye solo registros autorizados y contactos de los tutores vinculados.\nNo incluye usuarios, contraseñas, finanzas ni personal. No es una copia SQL restaurable.\nLos textos que podrían ejecutarse como fórmulas se prefijan con apóstrofo.\n\nFilas por archivo:\n".json_encode($resumen,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            if(!$zip->close()) throw new RuntimeException('No se pudo finalizar el ZIP.');
            return $archivo;
        } catch(\Throwable $e){try{$zip->close();}catch(\Throwable){} if(is_file($archivo))unlink($archivo);throw $e;}
        finally {foreach($temporales as $ruta)if(is_file($ruta))unlink($ruta);}
    }
}
