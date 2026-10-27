<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
/** Identidad profesional de estética, separada del veterinario y de la empresa. */
class ProfesionalEstetica extends Model {
 protected $table='profesionales_estetica';
 protected $guarded=['id'];
}
