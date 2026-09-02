<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromocionRedSocial extends Model
{
    protected $table = 'promociones_redes_sociales';

    protected $fillable = [
        'id_usuario', 'id_sitio_web', 'plan', 'nombre_plan', 'redes', 'duracion_dias',
        'monto', 'objetivo', 'estado', 'metodo_pago', 'referencia_pago',
        'comprobante', 'fecha_inicio', 'fecha_termino',
    ];

    protected $casts = [
        'redes' => 'array',
        'monto' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function sitioWeb(): BelongsTo
    {
        return $this->belongsTo(SitioWeb::class, 'id_sitio_web');
    }
}
