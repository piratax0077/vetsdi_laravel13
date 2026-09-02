<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SitioWebArticulo extends Model{protected $table='sitio_web_articulos';protected $guarded=[];protected $casts=['publicado'=>'boolean','publicado_at'=>'datetime'];public function sitio():BelongsTo{return $this->belongsTo(SitioWeb::class,'sitio_web_id');}}
