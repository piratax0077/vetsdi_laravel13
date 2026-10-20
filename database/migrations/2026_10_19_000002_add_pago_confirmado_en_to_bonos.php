<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('bonos', fn (Blueprint $t) => $t->timestamp('pago_confirmado_en')->nullable()->index()); }
    public function down(): void { Schema::table('bonos', function (Blueprint $t) { $t->dropIndex(['pago_confirmado_en']); $t->dropColumn('pago_confirmado_en'); }); }
};
