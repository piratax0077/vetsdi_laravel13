<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_uco_odontologia_veterinaria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_profesional')->nullable()->index();
            $table->unsignedBigInteger('id_lugar_atencion')->nullable()->index();
            $table->string('codigo', 40);
            $table->string('categoria', 100);
            $table->string('nombre', 190);
            $table->decimal('uco', 8, 2)->default(1);
            $table->unsignedInteger('valor_uco')->default(1000);
            $table->boolean('es_base')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['id_profesional', 'id_lugar_atencion', 'codigo'], 'tarifa_uco_prof_lugar_codigo');
        });

        $prestaciones = [
            ['ODO-EVA-01', 'Evaluacion y diagnostico', 'Consulta odontologica veterinaria integral', 1.00],
            ['ODO-EVA-02', 'Evaluacion y diagnostico', 'Periodontograma completo', 1.50],
            ['ODO-EVA-03', 'Evaluacion y diagnostico', 'Radiografia intraoral por pieza', 0.50],
            ['ODO-PRE-01', 'Prevencion', 'Profilaxis, destartraje y pulido dental', 3.00],
            ['ODO-PRE-02', 'Prevencion', 'Aplicacion de sellante dental', 1.00],
            ['ODO-PER-01', 'Periodoncia', 'Curetaje y alisado radicular por cuadrante', 2.00],
            ['ODO-PER-02', 'Periodoncia', 'Gingivectomia o gingivoplastia', 3.00],
            ['ODO-PER-03', 'Periodoncia', 'Cirugia periodontal por cuadrante', 4.00],
            ['ODO-RES-01', 'Restauradora', 'Restauracion dental', 2.00],
            ['ODO-END-01', 'Endodoncia', 'Endodoncia unirradicular', 5.00],
            ['ODO-END-02', 'Endodoncia', 'Endodoncia multirradicular', 7.00],
            ['ODO-CIR-01', 'Cirugia oral', 'Extraccion dental simple', 1.50],
            ['ODO-CIR-02', 'Cirugia oral', 'Extraccion dental quirurgica', 3.50],
            ['ODO-CIR-03', 'Cirugia oral', 'Extraccion de diente deciduo persistente', 2.00],
            ['ODO-CIR-04', 'Cirugia oral', 'Extraccion por lesion resortiva felina', 3.00],
            ['ODO-CIR-05', 'Cirugia oral', 'Biopsia de lesion oral', 3.00],
            ['ODO-CIR-06', 'Cirugia oral', 'Reseccion de masa oral', 6.00],
            ['ODO-ORT-01', 'Ortodoncia y protesis', 'Correccion de maloclusion', 4.00],
            ['ODO-ORT-02', 'Ortodoncia y protesis', 'Aparato ortodoncico veterinario', 8.00],
            ['ODO-ORT-03', 'Ortodoncia y protesis', 'Corona o protesis dental', 7.00],
            ['ODO-ANE-01', 'Anestesia y apoyo', 'Sedacion para procedimiento dental', 2.00],
            ['ODO-ANE-02', 'Anestesia y apoyo', 'Anestesia general odontologica', 4.00],
            ['ODO-ANE-03', 'Anestesia y apoyo', 'Bloqueo anestesico regional', 1.00],
            ['ODO-ANE-04', 'Anestesia y apoyo', 'Monitorizacion y recuperacion anestesica', 1.50],
        ];
        $ahora = now();
        DB::table('tarifas_uco_odontologia_veterinaria')->insert(array_map(fn ($p) => [
            'id_profesional' => null, 'id_lugar_atencion' => null, 'codigo' => $p[0],
            'categoria' => $p[1], 'nombre' => $p[2], 'uco' => $p[3], 'valor_uco' => 1000,
            'es_base' => true, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora,
        ], $prestaciones));
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_uco_odontologia_veterinaria');
    }
};
