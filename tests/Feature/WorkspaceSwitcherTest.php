<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CuentasService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WorkspaceSwitcherTest extends TestCase
{
    public function test_header_renders_legacy_roles_without_controller_context_variables(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === 'Profesional');
        $user->shouldReceive('hasAnyRole')->andReturn(true);
        $user->shouldReceive('nombreParaMostrar')->andReturn('Cuenta de prueba');
        Auth::setUser($user);
        $cuentas = \Mockery::mock(CuentasService::class);
        $cuentas->shouldReceive('rolesActivos')->with($user)->andReturn(collect());
        $this->app->instance(CuentasService::class, $cuentas);

        $html = view('template.partials.workspace_switcher')->render();
        $this->assertStringContainsString('Escritorio profesional', $html);
        $this->assertStringContainsString('Escritorio del asistente', $html);
        $this->assertStringContainsString('Escritorio Centro Veterinario', $html);
    }

    public function test_header_renders_without_an_authenticated_user(): void
    {
        Auth::forgetGuards();
        $this->assertStringNotContainsString('Cambiar de escritorio', view('template.partials.workspace_switcher')->render());
    }

    public function test_center_account_and_tutor_render_without_admin_context(): void
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->andReturn(false);
        $user->shouldReceive('hasAnyRole')->andReturnUsing(fn ($roles) => in_array('Adm_Institucion', $roles, true));
        $user->shouldReceive('nombreParaMostrar')->andReturn('Administración de prueba');
        Auth::setUser($user);
        $cuentas = \Mockery::mock(CuentasService::class);
        $cuentas->shouldReceive('rolesActivos')->with($user)->andReturn(collect([
            (object) ['tipo'=>'tutor','perfil_completo'=>true],
        ]));
        $this->app->instance(CuentasService::class, $cuentas);
        $html = view('template.partials.workspace_switcher')->render();
        $this->assertStringContainsString('Escritorio Centro Veterinario', $html);
        $this->assertStringContainsString('Tutor', $html);
        $this->assertStringNotContainsString('Escritorio profesional', $html);
    }
}
