<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {Schema::create('aranceles_profesionales',function(Blueprint $t){$t->id();$t->unsignedBigInteger('id_profesional')->unique();$t->longText('datos');$t->timestamps();});}
    public function down(): void {Schema::dropIfExists('aranceles_profesionales');}
};
