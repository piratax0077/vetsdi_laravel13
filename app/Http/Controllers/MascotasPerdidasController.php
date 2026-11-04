<?php

namespace App\Http\Controllers;

use App\Services\MascotasPerdidas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class MascotasPerdidasController extends Controller
{
    public function index(MascotasPerdidas $servicio)
    {
        return view('app.paciente.mascotas_perdidas', $servicio->pantalla());
    }

    public function publicar(Request $r, MascotasPerdidas $servicio)
    {
        return response()->json(['id' => $servicio->publicar($r), 'mensaje' => 'Aviso publicado en la red.']);
    }

    public function responder(Request $r, int $aviso, MascotasPerdidas $servicio)
    {
        return response()->json(['id' => $servicio->responder($r, $aviso), 'mensaje' => 'Respuesta enviada. El tutor tiene una notificación en Mis avisos.']);
    }

    public function compartir(int $respuesta, MascotasPerdidas $servicio)
    {
        $servicio->compartir($respuesta);
        return response()->json(['mensaje' => 'Contacto compartido y tutor notificado.']);
    }

    public function cerrar(int $aviso, MascotasPerdidas $servicio)
    {
        $servicio->cerrar($aviso);
        return response()->json(['mensaje' => 'Aviso cerrado. Notificamos a quienes respondieron.']);
    }

    public function leer(int $notificacion, MascotasPerdidas $servicio)
    {
        $n = DB::table('vet_perdidas_notificaciones')->where('id', $notificacion)->where('tutor_id', $servicio->tutor()->id);
        abort_unless((clone $n)->exists(), 404);
        $n->whereNull('leida_en')->update(['leida_en' => now(), 'updated_at' => now()]);
        return response()->json(['mensaje' => 'Notificación leída.']);
    }

    public function subir(Request $r, MascotasPerdidas $servicio)
    {
        $tutor = $servicio->tutor();
        $r->validate(['file' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:6144']);
        $ruta = $r->file('file')->store('mascotas-perdidas', 'local');
        try {
            $id = DB::table('vet_perdidas_fotos')->insertGetId(['tutor_id' => $tutor->id, 'ruta' => $ruta, 'created_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($ruta); throw $e;
        }
        return response()->json(['estado' => 1, 'img' => ['nombre_img' => (string)$id, 'ruta' => (string)$id, 'url' => route('paciente.perdidas.foto', $id)]]);
    }

    public function foto(int $foto, MascotasPerdidas $servicio)
    {
        $tutor = $servicio->tutor();
        $f = DB::table('vet_perdidas_fotos')->find($foto); abort_unless($f, 404);
        $permitida = $f->tutor_id == $tutor->id;
        if ($f->aviso_id) {
            $a = DB::table('vet_perdidas_avisos')->find($f->aviso_id);
            $permitida = $permitida || ($a && (!$a->cerrado_en || $a->tutor_id == $tutor->id));
        }
        if ($f->respuesta_id) {
            $r = DB::table('vet_perdidas_respuestas')->find($f->respuesta_id);
            $a = $r ? DB::table('vet_perdidas_avisos')->find($r->aviso_id) : null;
            $permitida = $permitida || ($a && $a->tutor_id == $tutor->id);
        }
        abort_unless($permitida, 403);
        abort_unless(Storage::disk('local')->exists($f->ruta), 404);
        return response()->file(Storage::disk('local')->path($f->ruta), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
