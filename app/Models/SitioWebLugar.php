<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitioWebLugar extends Model
{
    protected $table = 'sitio_web_lugares';

    protected $fillable = [
        'id_sitio_web',
        'id_lugar_atencion',
    ];

    public function sitio(): BelongsTo
    {
        return $this->belongsTo(SitioWeb::class, 'id_sitio_web');
    }

    public function lugarAtencion(): BelongsTo
    {
        return $this->belongsTo(LugarAtencion::class, 'id_lugar_atencion');
    }
}
