<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DocumentoPdfVeterinario extends Model
{
    protected $table='vet_documentos_pdf';
    protected $guarded=[];
    public $incrementing=false;
    protected $keyType='string';
}
