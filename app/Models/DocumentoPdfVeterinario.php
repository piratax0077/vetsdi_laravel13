<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
class DocumentoPdfVeterinario extends Model
{
    protected $table='vet_documentos_pdf';
    protected $guarded=[];
    public $incrementing=false;
    protected $keyType='string';

    /** Tipo legible del PDF, deducido de la ruta que lo generó y del nombre del archivo. */
    protected function tipo(): Attribute
    {
        return Attribute::get(function(){
            $texto=mb_strtolower(($this->origen ?? '').' '.($this->nombre ?? ''));
            foreach(['receta'=>'Receta','consentimiento'=>'Consentimiento informado','interconsulta'=>'Interconsulta',
                'examen'=>'Examen','laboratorio'=>'Examen','muestra'=>'Examen','reposo'=>'Certificado','licencia'=>'Certificado',
                'certificado'=>'Certificado','informe'=>'Informe','vacuna'=>'Vacuna','desparasit'=>'Desparasitación',
                'presupuesto'=>'Presupuesto','ficha'=>'Ficha'] as $clave=>$tipo) {
                if(str_contains($texto,$clave))return $tipo;
            }
            return 'Documento';
        });
    }
}
