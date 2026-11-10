<?php

namespace App\Services;

use App\Models\{EspecialidadesCm, SubTipoEspecialidad, TipoEspecialidad};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;

class EspecialidadesCentroVeterinario
{
    public const CATALOGO = [
        'cardiologia'=>'Cardiología veterinaria', 'cirugia'=>'Cirugía veterinaria',
        'dermatologia'=>'Dermatología veterinaria', 'fisiatria'=>'Fisiatría y rehabilitación veterinaria',
        'geriatria'=>'Geriatría veterinaria', 'medicina_felina'=>'Medicina felina',
        'medicina_interna'=>'Medicina interna veterinaria', 'nefrologia'=>'Nefrología veterinaria',
        'neurologia'=>'Neurología veterinaria', 'odontologia'=>'Odontología veterinaria',
        'oftalmologia'=>'Oftalmología veterinaria', 'oncologia'=>'Oncología veterinaria',
        'otorrinolaringologia'=>'Otorrinolaringología veterinaria', 'traumatologia'=>'Traumatología y ortopedia veterinaria',
        'gastroenterologia' => 'Consulta Gastroenterología',
        'broncopulmonar' => 'Consulta Broncopulmonar',
        'endocrinologia' => 'Consulta Endocrinología',
        'medicina_general_veterinaria' => 'Consulta Medicina General Veterinaria',
    ];

    public function guardar(Request $request): EspecialidadesCm
    {
        if ((string) $request->input('principal') === '0' && !$request->exists('num_profesionales')) $request->merge(['num_profesionales'=>1]);
        $datos=$request->validate([
            'especialidad'=>['required',Rule::in(array_keys(self::CATALOGO))],
            'num_profesionales'=>'required|integer|min:1', 'principal'=>'required|boolean',
        ]);
        $centro=app(PerfilCentroMedico::class)->centro($request);
        return DB::transaction(function () use ($centro,$datos) {
            DB::table($centro->getTable())->where('id',$centro->id)->lockForUpdate()->first();
            $subtipo = $this->resolverSubtipo($datos['especialidad']);
            $registro=EspecialidadesCm::where('id_institucion',$centro->id)->where('id_tipo_especialidad',8)
                ->where('id_sub_tipo_especialidad',$subtipo->id)->first() ?? new EspecialidadesCm();
            $registro->id_institucion=$centro->id;
            $registro->id_lugar_atencion=$centro->id_lugar_atencion;
            $registro->id_tipo_especialidad=8;
            $registro->id_sub_tipo_especialidad=$subtipo->id;
            $registro->num_profesionales=$datos['num_profesionales'];
            $registro->principal=$datos['principal'];
            $registro->id_admin=Auth::id();
            $registro->estado=1;
            $registro->saveOrFail();
            return $registro;
        });
    }

    public function resolverSubtipo(string $clave): SubTipoEspecialidad
    {
        return DB::transaction(function () use ($clave) {
            $tipo = TipoEspecialidad::lockForUpdate()->findOrFail(8);
            $subtipo = SubTipoEspecialidad::where('id_tipo_especialidad', $tipo->id)
                ->where('nombre', self::CATALOGO[$clave])->first() ?? new SubTipoEspecialidad();
            if (!$subtipo->exists) {
                $subtipo->id_tipo_especialidad = $tipo->id;
                $subtipo->nombre = self::CATALOGO[$clave];
                $subtipo->estado = 1;
                $subtipo->saveOrFail();
            }
            return $subtipo;
        });
    }

    public function nombres($registros)
    {
        return $registros->each(function ($registro) {
            if (in_array($registro->sub_tipo, self::CATALOGO, true)) $registro->nombre=$registro->sub_tipo;
        });
    }
}
