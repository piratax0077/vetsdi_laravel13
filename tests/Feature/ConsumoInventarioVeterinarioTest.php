<?php

namespace Tests\Feature;

use App\Models\{FichaAtencion, User};
use App\Services\ConsumoInventarioVeterinario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ConsumoInventarioVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) { $table->id(); $table->string('name'); });
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id(); $table->integer('id_usuario'); $table->string('nombre');
        });
        foreach (['sucursal', 'profesionales_lugares_atencion', 'asistentes_lugar_atencion', 'administrativos_lugar_atencion', 'especialidades_cm'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table) {
                $table->id(); $table->integer('id_lugar_atencion'); $table->integer('id_institucion')->nullable();
                $table->integer('id_profesional')->nullable(); $table->integer('estado')->default(1);
            });
        }
        foreach (['2026_11_18_000001_inventario_trazable_vet.php', '2026_11_18_000002_consumo_clinico_inventario.php', '2026_11_20_000002_despacho_clinico_inventario.php', '2026_11_20_000003_modalidad_consumo_inventario.php'] as $archivo) {
            (require database_path('migrations/'.$archivo))->up();
        }
        DB::table('users')->insert(['id'=>7, 'name'=>'Veterinario']);
        DB::table('profesionales')->insert(['id'=>3, 'id_usuario'=>7, 'nombre'=>'Veterinario']);
        DB::table('profesionales_lugares_atencion')->insert(['id_lugar_atencion'=>5,'id_institucion'=>24,'id_profesional'=>3]);
        $this->actingAs(User::findOrFail(7));
    }

    private function lote(string $titular, string $modalidad): int
    {
        return DB::table('vet_inventario_lotes')->insertGetId([
            'titular'=>$titular,'categoria'=>'insumo','producto'=>'Material odontológico',
            'condicion_venta'=>'libre','lote'=>'L1','vencimiento'=>now()->addYear()->toDateString(),
            'unidad'=>$modalidad === 'individual' ? 'unidad' : 'g','ubicacion'=>'Dental',
            'conservacion'=>'Según fabricante','saldo'=>10,'modalidad_consumo'=>$modalidad,
        ]);
    }

    private function consumir(int $lote, int $lugar, float $cantidad, int $mascota = 1): array
    {
        $ficha = (new FichaAtencion())->forceFill(['id'=>12+$mascota,'id_profesional'=>3,'id_lugar_atencion'=>$lugar,'id_mascota'=>$mascota]);
        return DB::transaction(fn () => app(ConsumoInventarioVeterinario::class)->registrar($ficha, [
            'lote_id'=>$lote,'cantidad_stock'=>$cantidad,'administrador_id'=>7,'despachador_id'=>7,'procedimiento'=>'Tratamiento dental',
        ]));
    }

    public function test_consumo_fraccionado_en_varios_pacientes_en_ambos_perfiles(): void
    {
        foreach (['profesional:3'=>99, 'centro:24'=>5] as $titular=>$lugar) {
            $lote = $this->lote($titular, 'fraccionable');
            $this->consumir($lote, $lugar, 0.125, 1);
            $registro = $this->consumir($lote, $lugar, 0.250, 2);
            $this->assertEquals(9.625, DB::table('vet_inventario_lotes')->where('id',$lote)->value('saldo'));
            $this->assertSame('Veterinario', $registro['indicado_por']);
            $this->assertSame('Veterinario', $registro['administrado_por']);
            $this->assertSame('Veterinario', $registro['despachado_por']);
            $this->assertSame('fraccionable', $registro['modalidad_consumo']);
            $this->assertEquals(2, DB::table('vet_inventario_movimientos')->where('lote_id',$lote)->count());
        }
    }

    public function test_material_individual_no_se_fracciona_en_ambos_perfiles(): void
    {
        foreach (['profesional:3'=>99, 'centro:24'=>5] as $titular=>$lugar) {
            $lote = $this->lote($titular, 'individual');
            try {
                $this->consumir($lote,$lugar,0.5);
                $this->fail('No debe fraccionarse el material individual.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('cantidad_stock',$error->errors());
            }
            $this->assertEquals(10, DB::table('vet_inventario_lotes')->where('id',$lote)->value('saldo'));
            $this->assertSame(0, DB::table('vet_inventario_movimientos')->where('lote_id',$lote)->count());
            $this->consumir($lote,$lugar,1);
            $this->assertEquals(9, DB::table('vet_inventario_lotes')->where('id',$lote)->value('saldo'));
        }
    }

    public function test_no_permite_consumir_el_inventario_de_otro_profesional(): void
    {
        $lote = $this->lote('profesional:99','fraccionable');
        try {
            $this->consumir($lote,99,1);
            $this->fail('Debe rechazar el inventario ajeno.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(403,$error->getStatusCode());
        }
        $this->assertEquals(10, DB::table('vet_inventario_lotes')->where('id',$lote)->value('saldo'));
    }
}
