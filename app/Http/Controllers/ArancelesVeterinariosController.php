<?php
namespace App\Http\Controllers;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ArancelesVeterinariosController extends Controller
{
    private function profesional(): Profesional {
        abort_unless(Auth::user()?->hasRole('Profesional'),403);
        return Profesional::where('id_usuario',Auth::id())->firstOrFail();
    }
    public static function inicial(): array {
        return ['aranceles'=>[],'examenes'=>[],'procedimientos'=>[],'config'=>[
            'lugares'=>[['id'=>'consulta','nombre'=>'Consulta','recargo'=>0],['id'=>'domicilio','nombre'=>'Domicilio','recargo'=>0],['id'=>'urgencia','nombre'=>'Urgencia / nocturno','recargo'=>0]],
            'tamanos'=>array_map(fn($id,$nombre)=>['id'=>$id,'nombre'=>$nombre,'pesoDesde'=>null,'pesoHasta'=>null],['mini','pequeno','mediano','grande','gigante'],['Mini','Pequeño','Mediano','Grande','Gigante']),
            'parametros'=>['iva'=>19,'retencion'=>0]]];
    }
    public function listar() {
        $prof=$this->profesional();$fila=DB::table('aranceles_profesionales')->where('id_profesional',$prof->id)->first();
        return response()->json($fila?json_decode($fila->datos,true):self::inicial());
    }
    public function procedimientos() {
        $prof = $this->profesional();
        $fila = DB::table('aranceles_profesionales')->where('id_profesional', $prof->id)->first();
        return response()->json(['estado' => 1, 'registro' => app(\App\Services\ProcedimientosArancelados::class)->opciones($fila ? json_decode($fila->datos, true) : self::inicial())]);
    }
    private function validarItem(array $item): array {
        Validator::make($item,[
            'codigo'=>'required|string|max:20','nombre'=>'required|string|max:120','activo'=>'required|boolean','variaPorTamano'=>'required|boolean',
            'valorNeto'=>'required|integer|min:0|max:999999999','afectoIva'=>'required|boolean','costoInterno'=>'nullable|integer|min:0|max:999999999',
            'preciosTamano'=>'present|array|max:30','preciosTamano.*.tamanoId'=>'required|string|max:50','preciosTamano.*.valorNeto'=>'required|integer|min:0|max:999999999',
            'preciosTamano.*.activo'=>'required|boolean','preciosTamano.*.afectoIva'=>'required|boolean','preciosTamano.*.costoInterno'=>'nullable|integer|min:0|max:999999999',
            'tipo'=>'nullable|string|max:100','categoria'=>'nullable|string|max:100','descripcion'=>'nullable|string|max:300',
            'duracionMin'=>'nullable|integer|min:1|max:1440','requiereAnestesia'=>'sometimes|boolean','insumos'=>'nullable|string|max:3000',
            'especie'=>'nullable|string|max:100','lugarId'=>'nullable|string|max:100','vigenteDesde'=>'nullable|date_format:Y-m-d','vigenteHasta'=>'nullable|date_format:Y-m-d',
            'realizacion'=>'nullable|in:propio,externo','laboratorio'=>'nullable|string|max:255','muestra'=>'nullable|string|max:255','tiempoEntrega'=>'nullable|string|max:255',
            'preparacion'=>'nullable|string|max:3000','costoLaboratorio'=>'nullable|integer|min:0|max:999999999',
        ])->validate();
        unset($item['id']);$item['codigo']=mb_strtoupper(trim($item['codigo']));
        return $item;
    }
    private function validarConfig(array $config): array {
        return Validator::make($config,[
            'lugares'=>'required|array|max:50','lugares.*.id'=>'required|string|max:50|distinct','lugares.*.nombre'=>'required|string|max:100','lugares.*.recargo'=>'nullable|numeric|min:0|max:1000',
            'tamanos'=>'required|array|min:1|max:30','tamanos.*.id'=>'required|string|max:50|distinct','tamanos.*.nombre'=>'required|string|max:100','tamanos.*.pesoDesde'=>'nullable|numeric|min:0','tamanos.*.pesoHasta'=>'nullable|numeric|min:0',
            'parametros'=>'required|array','parametros.iva'=>'required|numeric|min:0|max:100','parametros.retencion'=>'required|numeric|min:0|max:100',
        ])->validate();
    }
    public function guardar(Request $request) {
        $prof=$this->profesional();$entrada=$request->validate(['accion'=>'required|in:crear,editar,estado,config,importar','catalogo'=>'nullable|required_if:accion,crear,editar,estado|in:aranceles,examenes,procedimientos','id'=>'nullable|required_if:accion,editar,estado|string|max:100','datos'=>'nullable|required_unless:accion,estado|array','activo'=>'nullable|required_if:accion,estado|boolean']);
        return DB::transaction(function()use($prof,$entrada){
            Profesional::whereKey($prof->id)->lockForUpdate()->firstOrFail();
            $fila=DB::table('aranceles_profesionales')->where('id_profesional',$prof->id)->first();$estado=$fila?json_decode($fila->datos,true):self::inicial();
            if($entrada['accion']==='importar') {
                if(count($estado['aranceles'])+count($estado['examenes'])+count($estado['procedimientos'])>0)throw ValidationException::withMessages(['datos'=>'La cuenta ya tiene aranceles. La importación no reemplaza precios guardados.']);
                $importado=$entrada['datos'];$codigos=[];
                $estado['config']=$this->validarConfig($importado['config']??[]);
                foreach(['aranceles','examenes','procedimientos'] as $cat){
                    $items=$importado[$cat]??[];
                    Validator::make(['items'=>$items],['items'=>'present|array|max:2000'])->validate();
                    foreach($items as $item){
                        $item=$this->validarItem($item);
                        if(in_array($item['codigo'],$codigos,true))throw ValidationException::withMessages(['codigo'=>'Hay códigos duplicados en los datos del navegador.']);
                        $codigos[]=$item['codigo'];$item['id']=(string)Str::uuid();$estado[$cat][]=$item;
                    }
                }
                $resultado=['importado'=>true];
            }
            elseif($entrada['accion']==='config'){$resultado=$this->validarConfig($entrada['datos']);$estado['config']=$resultado;}
            else {
                $catalogo=$entrada['catalogo'];$id=$entrada['id']??null;$indice=null;
                foreach($estado[$catalogo] as $i=>$item)if($item['id']===$id)$indice=$i;
                if($entrada['accion']!=='crear')abort_if($indice===null,404);
                if($entrada['accion']==='estado'){$estado[$catalogo][$indice]['activo']=(bool)$entrada['activo'];$resultado=$estado[$catalogo][$indice];}
                else {
                    $nuevo=$this->validarItem($entrada['datos']);
                    foreach(['aranceles','examenes','procedimientos'] as $cat)foreach($estado[$cat] as $item)if($item['id']!==$id && mb_strtoupper($item['codigo'])===$nuevo['codigo'])throw ValidationException::withMessages(['codigo'=>'Ese código ya está en uso en tus aranceles.']);
                    $nuevo['id']=$id??(string)Str::uuid();
                    if($indice===null)$estado[$catalogo][]=$nuevo;else $estado[$catalogo][$indice]=$nuevo;
                    $resultado=$nuevo;
                }
            }
            if($fila)DB::table('aranceles_profesionales')->where('id',$fila->id)->update(['datos'=>json_encode($estado),'updated_at'=>now()]);
            else DB::table('aranceles_profesionales')->insert(['id_profesional'=>$prof->id,'datos'=>json_encode($estado),'created_at'=>now(),'updated_at'=>now()]);
            return response()->json($resultado);
        });
    }
}
