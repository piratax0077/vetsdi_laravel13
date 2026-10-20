<?php
namespace App\Services;
use App\Models\Direccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB,Schema};
class DireccionesPerfil
{
    /** Una edición de perfil no debe cambiar la residencia de otros registros. */
    public static function editable(Model $perfil): Direccion
    {
        $direccion=Direccion::find($perfil->id_direccion) ?? new Direccion();
        if (!$direccion->exists) return $direccion;
        foreach (['pacientes','profesionales','asistentes','instituciones','servicios','admin_inst_serv','lugares_atencion','contactos_emergencia'] as $tabla) {
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla,'id_direccion')) continue;
            $consulta=DB::table($tabla)->where('id_direccion',$direccion->id);
            if ($tabla===$perfil->getTable()) $consulta->where('id','<>',$perfil->id);
            if ($consulta->exists()) return $direccion->replicate();
        }
        return $direccion;
    }
}
