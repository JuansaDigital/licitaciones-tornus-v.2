<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
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
        ]);

        $response = $this->post('/login', [
            'email' => 'test_agent@tornus.agency',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('market.select'));
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
}
