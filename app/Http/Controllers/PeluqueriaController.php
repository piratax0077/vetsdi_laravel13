<?php
namespace App\Http\Controllers;
use App\Models\Peluqueria;
use App\Services\CuentasService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB};
use Illuminate\Validation\ValidationException;
class PeluqueriaController extends Controller
{
    private function perfil(): Peluqueria {
        abort_unless(app(CuentasService::class)->tieneRol(Auth::user(),'peluqueria'),403);
        return Peluqueria::where('id_usuario',Auth::id())->firstOrFail();
    }
    public function index(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['fecha'=>'nullable|date_format:Y-m-d']);
        $fecha=$datos['fecha']??now()->toDateString();
        $clientes=DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->orderBy('mascota')->get();
        $citas=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->whereDate('c.inicio',$fecha)->orderBy('c.inicio')
            ->select('c.*','m.mascota','m.tutor','m.telefono','m.cuidados')->get();
        $historial=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->where('c.estado','finalizada')->orderByDesc('c.finalizada_en')
            ->select('c.*','m.mascota','m.tutor')->paginate(15);
        return view('app.peluqueria.index',compact('perfil','fecha','clientes','citas','historial'));
    }
    public function perfilGuardar(Request $request) {
        $perfil=$this->perfil();$datos=$request->validate(['nombre'=>'required|string|max:255','telefono'=>'required|string|max:30','direccion'=>'required|string|max:255']);
        $perfil->fill($datos)->saveOrFail();return back()->with('mensaje','Perfil actualizado.');
    }
    public function clienteGuardar(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['tutor'=>'required|string|max:255','telefono'=>'required|string|max:30','email'=>'nullable|email|max:255','mascota'=>'required|string|max:100','especie'=>'required|string|max:60','raza'=>'nullable|string|max:100','cuidados'=>'nullable|string|max:3000']);
        DB::table('peluqueria_clientes')->insert($datos+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('mensaje','Tutor y mascota registrados.');
    }
    public function citaGuardar(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['cliente_id'=>'required|integer','inicio'=>'required|date_format:Y-m-d\TH:i|after_or_equal:now','duracion'=>'required|integer|min:15|max:480','servicio'=>'required|in:Baño,Belleza,Peluquería','valor'=>'required|integer|min:0|max:999999999','observaciones'=>'nullable|string|max:3000']);
        DB::transaction(function()use($perfil,$datos){
            Peluqueria::whereKey($perfil->id)->lockForUpdate()->firstOrFail();
            abort_unless(DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('id',$datos['cliente_id'])->exists(),404);
            $inicio=Carbon::parse($datos['inicio']);$fin=$inicio->copy()->addMinutes((int)$datos['duracion']);
            if(DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('estado','<>','cancelada')->where('inicio','<',$fin)->where('fin','>',$inicio)->exists())
                throw ValidationException::withMessages(['inicio'=>'Ya existe una reserva en ese horario.']);
            unset($datos['duracion']);$datos['inicio']=$inicio;$datos['fin']=$fin;
            DB::table('peluqueria_citas')->insert($datos+['peluqueria_id'=>$perfil->id,'estado'=>'reservada','created_at'=>now(),'updated_at'=>now()]);
        });
        return back()->with('mensaje','Hora reservada.');
    }
    public function estado(Request $request,int $cita) {
        $perfil=$this->perfil();$datos=$request->validate(['estado'=>'required|in:confirmada,en_atencion,finalizada,cancelada','registro'=>'nullable|string|max:5000']);
        DB::transaction(function()use($perfil,$cita,$datos){
            $actual=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->lockForUpdate()->first();abort_unless($actual,404);
            $permitidos=['reservada'=>['confirmada','en_atencion','cancelada'],'confirmada'=>['en_atencion','cancelada'],'en_atencion'=>['finalizada','cancelada']];
            if(!in_array($datos['estado'],$permitidos[$actual->estado]??[],true))throw ValidationException::withMessages(['estado'=>'Ese cambio de estado no está permitido.']);
            if($datos['estado']==='finalizada' && trim($datos['registro']??'')==='')throw ValidationException::withMessages(['registro'=>'Describe el servicio realizado antes de finalizar.']);
            DB::table('peluqueria_citas')->where('id',$cita)->update(['estado'=>$datos['estado'],'registro'=>$datos['registro']??$actual->registro,'finalizada_en'=>$datos['estado']==='finalizada'?now():null,'updated_at'=>now()]);
        });
        return back()->with('mensaje','Atención actualizada.');
    }
}
