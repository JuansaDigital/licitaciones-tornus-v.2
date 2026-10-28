<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TornusPlatformTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Asegurar que las tablas existen para pruebas
        Artisan::call('migrate', ['--database' => 'sqlite']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_login_screen_is_separate_without_country_selector(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Tornus');
        $response->assertSee('Iniciar Sesión');
        $response->assertSee('Correo Corporativo');
        $response->assertSee('Contraseña');
        // El login ya no debe contener selector de país
        $response->assertDontSee('name="market"', false);
    }

    public function test_user_can_login_and_is_redirected_to_select_country_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'test_agent@tornus.agency',
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test_agent@tornus.agency',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('market.select'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@tornus.agency',
            'password' => bcrypt('password123'),
            'activo' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@tornus.agency',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_view_country_selection_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('market.select'));
        $response->assertStatus(200);
        $response->assertSee('Selecciona el Mercado Operativo');
        $response->assertSee('Colombia');
        $response->assertSee('Perú');
        $response->assertSee('OPERAR EN COLOMBIA');
        $response->assertSee('OPERAR EN PERÚ');
        $response->assertSee('Configuración');
    }

    public function test_user_can_select_country_and_be_redirected_to_radar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('market.set', 'CO'));
        $response->assertRedirect(route('radar'));
        $response->assertSessionHas('market', 'CO');

        $response = $this->actingAs($user)->get(route('market.set', 'PE'));
        $response->assertRedirect(route('radar'));
        $response->assertSessionHas('market', 'PE');
    }

    public function test_authenticated_user_can_access_radar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/radar');
        $response->assertStatus(200);
        $response->assertSee('Radar en Vivo');
    }

    public function test_authenticated_user_can_access_cartera(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cartera');
        $response->assertStatus(200);
        $response->assertSee('Mi Cartera Comercial');
    }

    public function test_authenticated_user_can_access_afinidad(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/afinidad');
        $response->assertStatus(200);
        $response->assertSee('Calificación de Afinidad con Inteligencia Artificial');
    }

    public function test_calendar_feed_is_publicly_accessible(): void
    {
        $response = $this->get(route('calendar.feed', 'CO'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
    }

    public function test_admin_can_access_configuracion_and_see_team(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('configuracion'));
        $response->assertStatus(200);
        $response->assertSee('EQUIPO CON ACCESO AUTORIZADO');
        $response->assertSee('TOTAL MIEMBROS');
        $response->assertSee('USUARIOS ACTIVOS');
        $response->assertSee('ADMINISTRADORES');
    }

    public function test_admin_can_add_new_collaborator_and_change_password(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'activo' => true,
        ]);

        $postResponse = $this->actingAs($admin)->post(route('configuracion.usuarios.store'), [
            'name' => 'Alejandro Rodríguez',
            'email' => 'alejandro@tornus.agency',
            'rol' => 'colaborador',
            'password' => 'clave123',
        ]);

        $postResponse->assertSessionHas('status');
        $newMember = User::where('email', 'alejandro@tornus.agency')->first();
        $this->assertNotNull($newMember);
        $this->assertEquals('colaborador', $newMember->rol);
        $this->assertTrue(Hash::check('clave123', $newMember->password));

        // Actualizar clave
        $passResponse = $this->actingAs($admin)->post(route('configuracion.usuarios.password', $newMember->id), [
            'password' => 'nuevaClave456',
            'password_confirmation' => 'nuevaClave456',
        ]);
        $passResponse->assertSessionHas('status');
        $newMember->refresh();
        $this->assertTrue(Hash::check('nuevaClave456', $newMember->password));
    }
}
