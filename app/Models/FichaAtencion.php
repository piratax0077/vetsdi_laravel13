<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use App\Models\PresupuestoMascota;



class FichaAtencion extends Model

{

    use HasFactory;

    protected $table = 'fichas_atenciones';

    protected static function booted()
    {
        static::saved(function (FichaAtencion $ficha) {
            if ((int) $ficha->finalizada !== 1) {
                return;
            }

            $horasMedicas = HoraMedica::where('id_ficha_atencion', $ficha->id)->pluck('id');
            if ($horasMedicas->isEmpty()) {
                return;
            }

            Bono::whereIn('id_referencia', $horasMedicas)
                ->where('id_tipo_bono', 1)
                ->where('estado_consulta', 2)
                ->where('devuelto', 0)
                ->update([
                    'estado_consulta' => 6,
                    'glosa' => 'QR RECIBIDO - ATENCION FINALIZADA',
                    'updated_at' => now(),
                ]);
        });
    }



    public function Paciente()

    {

        return $this->hasOne(Paciente::class, 'id', 'id_paciente');

    }



    public function Profesional()

    {

        return $this->hasOne(Profesional::class, 'id', 'id_profesional');

    }



    public function Licencias()

    {

        // return $this->belongsToMany(Licencia::class, 'licencias_ppf', 'id_ficha_atencion', 'id_licencia');
        return $this->hasMany(Licencia::class, 'id_ficha_atencion', 'id' );

    }



    public function Recetas()

    {

        //return $this->belongsToMany(DetalleReceta::class, 'id_ficha', 'id');

		return $this->hasMany(DetalleReceta::class, 'id_ficha', 'id');

    }



    public function Examenes()

    {

        return $this->belongsTo(ExamenPPF::class, 'id', 'id_ficha_atencion');

    }

    public function LugarAtencion()

    {

        return $this->hasOne(LugarAtencion::class, 'id', 'id_lugar_atencion');

    }

    public function PresupuestosMascota()
    {
        return $this->hasMany(PresupuestoMascota::class, 'id_ficha_atencion', 'id');
    }

}
