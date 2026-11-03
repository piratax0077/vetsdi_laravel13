<?php
namespace App\Services;

use App\Models\Profesional;
use Illuminate\Support\Facades\{DB,Schema};

class ExportacionPacientesProfesional
{
    private function disponible(string $tabla,array $columnas): bool
    {
        return Schema::hasTable($tabla) && !array_diff($columnas,Schema::getColumnListing($tabla));
    }

    public function consultas(Profesional $profesional): array
    {
        $consultas=[];
        foreach(['horas_medicas','fichas_atenciones'] as $tabla) {
            if($this->disponible($tabla,['id','id_mascota','id_profesional'])) $consultas[$tabla]=DB::table($tabla)->where('id_profesional',$profesional->id)->whereNotNull('id_mascota');
        }
        if($this->disponible('vet_reservas_servicios',['id','id_mascota','id_usuario'])) {
            $campo=Schema::hasColumn('vet_reservas_servicios','id_profesional') ? 'id_profesional' : 'id_usuario';
            $consultas['vet_reservas_servicios']=DB::table('vet_reservas_servicios')->where($campo,$campo === 'id_profesional' ? $profesional->id : $profesional->id_usuario);
        }
        if($this->disponible('vet_internaciones',['id','id_institucion','id_mascota']) && $this->disponible('vet_internacion_profesionales',['internacion_id','id_usuario']) && $this->disponible('profesionales_lugares_atencion',['id_profesional','id_institucion','estado'])) {
            $consultas['vet_internaciones']=DB::table('vet_internaciones')->whereIn('id',DB::table('vet_internacion_profesionales')->where('id_usuario',$profesional->id_usuario)->select('internacion_id'))->whereIn('id_institucion',DB::table('profesionales_lugares_atencion')->where('id_profesional',$profesional->id)->where('estado',1)->select('id_institucion'));
        }
        $fuentes=$consultas;
        $mascotas=DB::table('mascotas')->where(function($q) use($fuentes){
            $q->whereRaw('1 = 0');
            foreach($fuentes as $fuente)$q->orWhereIn('id',(clone $fuente)->reorder()->select('id_mascota'));
        });
        $campos=array_values(array_intersect(['id','nombre','especie','raza','sexo','fecha_nacimiento','fecha_nac','color','microchip','numero_microchip','esterilizado','peso','id_responsable','created_at','updated_at'],Schema::getColumnListing('mascotas')));
        $consultas=['pacientes_mascotas'=>(clone $mascotas)->select($campos)]+$consultas;
        if($this->disponible('pacientes',['id']) && Schema::hasColumn('mascotas','id_responsable')) {
            $campos=array_values(array_intersect(['id','rut','nombre','apellido_uno','apellido_dos','apellido_paterno','apellido_materno','email','telefono_uno','telefono_dos','telefono','created_at'],Schema::getColumnListing('pacientes')));
            $consultas['tutores']=DB::table('pacientes')->whereIn('id',(clone $mascotas)->select('id_responsable'))->select($campos);
        }
        if(isset($consultas['vet_internaciones'])) {
            foreach(['vet_evoluciones','vet_controles_hospitalarios'] as $tabla) {
                if($this->disponible($tabla,['id','internacion_id','id_usuario'])) $consultas[$tabla]=DB::table($tabla)->where('id_usuario',$profesional->id_usuario)->whereIn('internacion_id',(clone $consultas['vet_internaciones'])->select('id'));
            }
        }
        return $consultas;
    }

    public function crear(Profesional $profesional): string
    {
        return app(ExportacionPacientesCentro::class)->crearArchivo($this->consultas($profesional),"Profesional: {$profesional->nombre} {$profesional->apellido} (ID {$profesional->id})\nAtenciones propias; hospitalización limitada a pacientes asignados y registros realizados por este usuario.");
    }
}
