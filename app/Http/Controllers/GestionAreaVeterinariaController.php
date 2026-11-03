<?php

namespace App\Http\Controllers;

use App\Services\OperacionVeterinaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;

class GestionAreaVeterinariaController extends Controller
{
    public function index(Request $r, OperacionVeterinaria $s, string $area)
    {
        abort_unless(isset(OperacionVeterinaria::AREAS[$area]),404);
        $institucion = $s->institucion($r);
        $base = fn($tabla)=>DB::table($tabla)->where('id_institucion',$institucion->id)->where('area',$area);
        $responsable = $base('vet_responsables_area')->first();
        $totales = $base('vet_gastos_area')->selectRaw('tipo, SUM(monto) as total')->groupBy('tipo')->pluck('total','tipo');
        $gastos = $base('vet_gastos_area')->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->withQueryString();
        $titulo = OperacionVeterinaria::AREAS[$area];
        return view('app.veterinaria.areas.gestion',compact('institucion','area','responsable','totales','gastos','titulo'));
    }

    public function guardar(Request $r, OperacionVeterinaria $s, string $area)
    {
        abort_unless(isset(OperacionVeterinaria::AREAS[$area]),404);
        $institucion = $s->institucion($r);
        $r->validate(['accion'=>['required',Rule::in(['responsable','gasto'])]]);
        $scope = ['id_institucion'=>$institucion->id,'area'=>$area];
        if ($r->accion === 'responsable') {
            $d = $r->validate(['nombre'=>'required|string|max:150','contacto'=>'nullable|string|max:150']);
            $existente = DB::table('vet_responsables_area')->where($scope)->first();
            DB::table('vet_responsables_area')->updateOrInsert($scope,$d+['updated_at'=>now(),'created_at'=>$existente->created_at ?? now()]);
        } else {
            $d = $r->validate(['tipo'=>['required',Rule::in(['insumos','otros'])], 'fecha'=>'required|date', 'concepto'=>'required|string|max:200', 'proveedor'=>'nullable|string|max:150', 'comprobante'=>'nullable|string|max:100', 'monto'=>'required|integer|min:1|max:1000000000']);
            DB::table('vet_gastos_area')->insert($scope+$d+['id_usuario'=>Auth::id(),'created_at'=>now(),'updated_at'=>now()]);
        }
        return redirect()->route('vet.areas.gestion',['area'=>$area,'id_institucion'=>$institucion->id])->with('gestion_guardada','Cambios guardados correctamente.');
    }
}
