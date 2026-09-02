<?php
namespace App\Http\Controllers;
use App\Models\{SitioWeb,SitioWebArticulo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class SitioWebArticuloController extends Controller{
 private function sitio():SitioWeb{return SitioWeb::where('id_usuario',Auth::id())->firstOrFail();}
 public function index(){return view('sitio.articulos.index',['sitio'=>$this->sitio(),'articulos'=>$this->sitio()->hasMany(SitioWebArticulo::class,'sitio_web_id')->latest()->get()]);}
 public function create(){return view('sitio.articulos.form',['sitio'=>$this->sitio(),'articulo'=>new SitioWebArticulo]);}
 public function edit(SitioWebArticulo $articulo){abort_unless($articulo->sitio_web_id===$this->sitio()->id,403);return view('sitio.articulos.form',['sitio'=>$this->sitio(),'articulo'=>$articulo]);}
 public function store(Request $r){$articulo=new SitioWebArticulo(['sitio_web_id'=>$this->sitio()->id]);return $this->save($r,$articulo);}
 public function update(Request $r,SitioWebArticulo $articulo){abort_unless($articulo->sitio_web_id===$this->sitio()->id,403);return $this->save($r,$articulo);}
 public function destroy(SitioWebArticulo $articulo){abort_unless($articulo->sitio_web_id===$this->sitio()->id,403);$articulo->delete();return back()->with('mensaje','Artículo eliminado.');}
 public function show(string $slug){$articulo=SitioWebArticulo::with('sitio.usuario.profesional')->where('slug',$slug)->where('publicado',true)->firstOrFail();return view('sitio.articulos.show',compact('articulo'));}
 private function save(Request $r,SitioWebArticulo $articulo){$d=$r->validate(['titulo'=>'required|string|max:180','resumen'=>'nullable|string|max:300','contenido'=>'required|string|min:40','imagen'=>'nullable|image|max:5120','publicado'=>'nullable|boolean']);if($r->hasFile('imagen'))$d['imagen']=$r->file('imagen')->store('sitio-articulos','public');$d['slug']=Str::slug($d['titulo']).'-'.($articulo->exists?$articulo->id:Str::lower(Str::random(5)));$d['publicado']=$r->boolean('publicado');$d['publicado_at']=$d['publicado']?($articulo->publicado_at?:now()):null;$articulo->fill($d)->save();return redirect()->route('sitio.articulos.index')->with('mensaje','Artículo guardado correctamente.');}
}
