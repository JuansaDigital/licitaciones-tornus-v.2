<?php

namespace Tests\Feature;

use App\Models\Licitacion;
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

    public function test_saving_licitacion_to_cartera_displays_in_kanban_board(): void
    {
        $user = User::factory()->create();
        $lic = Licitacion::on('colombia')->create([
            'external_id' => 'TEST.CARTERA.KANBAN',
            'referencia' => 'REF-KANBAN-99',
            'entidad' => 'FONTUR TEST KANBAN',
            'objeto' => 'Promoción turística del destino Caribe',
            'departamento' => 'Bolívar',
            'cuantia' => 120000000,
            'moneda' => 'COP',
            'en_cartera' => false,
            'cartera_estado' => 'Por Analizar',
        ]);

        // Guardar en cartera vía endpoint
        $this->actingAs($user)->post(route('radar.cartera', $lic->id))->assertRedirect();

        // Verificar que aparece visible en el Kanban de /cartera bajo 'Identificada'
        $response = $this->actingAs($user)->get('/cartera');
        $response->assertStatus(200);
        $response->assertSee('REF-KANBAN-99');
        $response->assertSee('Promoción turística del destino Caribe');

        $lic->delete();
    }

    public function test_authenticated_user_can_access_afinidad(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/afinidad');
        $response->assertStatus(200);
        $response->assertSee('Propuestas analizadas con IA');
    }

    public function test_can_delete_analyzed_proposal_individually(): void
    {
        $user = User::factory()->create();
        $lic = Licitacion::on('colombia')->create([
            'external_id' => 'TEST.DEL.1',
            'referencia' => 'REF-DEL-1',
            'entidad' => 'Entidad Test',
            'objeto' => 'Servicios de publicidad y mercadeo',
            'departamento' => 'Antioquia',
            'cuantia' => 50000000,
            'moneda' => 'COP',
            'tipo_proceso' => 'Selección Abreviada',
            'score' => 85,
            'nivel' => 'ALTA',
            'evaluado_en' => now(),
            'en_cartera' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('afinidad.destroy', $lic->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('licitaciones', ['id' => $lic->id], 'colombia');
    }

    public function test_can_bulk_delete_analyzed_proposals(): void
    {
        $user = User::factory()->create();
        $lic1 = Licitacion::on('colombia')->create([
            'external_id' => 'TEST.BULK.1',
            'referencia' => 'REF-BULK-1',
            'entidad' => 'Entidad Test 1',
            'objeto' => 'Campaña turística 1',
            'score' => 75,
            'nivel' => 'ALTA',
            'evaluado_en' => now(),
            'en_cartera' => false,
        ]);
        $lic2 = Licitacion::on('colombia')->create([
            'external_id' => 'TEST.BULK.2',
            'referencia' => 'REF-BULK-2',
            'entidad' => 'Entidad Test 2',
            'objeto' => 'Campaña turística 2',
            'score' => 80,
            'nivel' => 'ALTA',
            'evaluado_en' => now(),
            'en_cartera' => false,
        ]);

        $response = $this->actingAs($user)->post(route('afinidad.bulk-destroy'), [
            'ids' => [$lic1->id, $lic2->id]
        ]);
        $response->assertRedirect();
        $this->assertDatabaseMissing('licitaciones', ['id' => $lic1->id], 'colombia');
        $this->assertDatabaseMissing('licitaciones', ['id' => $lic2->id], 'colombia');
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

    public function test_radar_displays_accordions_for_keywords_and_exclusions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('perfil', ['tab' => 'filtros', 'country' => 'CO']));
        $response->assertStatus(200);
        $response->assertSee('Palabras Clave de Búsqueda (Colombia)');
        $response->assertSee('Términos de Exclusión Automática (Colombia)');
        $response->assertSee('AGREGAR PALABRA CO');
        $response->assertSee('AGREGAR EXCLUSIÓN CO');
    }

    public function test_keywords_and_exclusions_management(): void
    {
        $user = User::factory()->create();

        // Agregar palabra clave
        $this->actingAs($user)->post(route('radar.keywords.add'), [
            'keyword' => 'TURISMO SOSTENIBLE'
        ])->assertSessionHas('status');

        // Editar palabra clave
        $this->actingAs($user)->post(route('radar.keywords.update'), [
            'old_keyword' => 'TURISMO SOSTENIBLE',
            'keyword' => 'ECOTURISMO AVANZADO'
        ])->assertSessionHas('status');

        // Eliminar palabra clave
        $this->actingAs($user)->post(route('radar.keywords.remove'), [
            'keyword' => 'ECOTURISMO AVANZADO'
        ])->assertSessionHas('status');

        // Agregar exclusión
        $this->actingAs($user)->post(route('radar.exclusions.add'), [
            'exclusion' => 'FERRETERIA'
        ])->assertSessionHas('status');

        // Eliminar exclusión
        $this->actingAs($user)->post(route('radar.exclusions.remove'), [
            'exclusion' => 'FERRETERIA'
        ])->assertSessionHas('status');
    }

    public function test_authenticated_user_can_access_and_update_perfil_tornus(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('perfil'));
        $response->assertStatus(200);
        $response->assertSee('Perfil Corporativo e Inteligencia Estratégica');
        $response->assertSee('GUARDAR PERFIL ESTRATÉGICO');

        $updateResponse = $this->actingAs($user)->post(route('perfil.update'), [
            'nombre_agencia' => 'Tornus Agency Test',
            'sitio_web' => 'https://tornus.agency',
            'propuesta_valor' => 'Especialistas en turismo y marketing de territorio.',
            'servicios_principales' => "Branding territorial\nPauta digital",
            'entidades_objetivo' => "FONTUR\nMinCIT",
            'criterios_exclusion' => "Obras civiles\nVigilancia",
            'rango_presupuesto_ideal' => 'Proyectos desde $50,000,000 COP',
        ]);

        $updateResponse->assertSessionHas('status');
    }

    public function test_user_can_update_email_alert_settings_and_toggle_notification(): void
    {
        $user = User::factory()->create([
            'rol' => 'administrador',
            'notificar_email' => false,
            'notificar_criterio' => 'todas',
            'notificar_mercado' => 'TODOS',
        ]);

        // Guardar preferencias
        $response = $this->actingAs($user)->post(route('configuracion.alertas.update'), [
            'notificar_email' => '1',
            'notificar_criterio' => 'afinidad_alta',
            'notificar_mercado' => 'CO',
        ]);

        $response->assertSessionHas('status');
        $user->refresh();
        $this->assertTrue($user->notificar_email);
        $this->assertEquals('afinidad_alta', $user->notificar_criterio);
        $this->assertEquals('CO', $user->notificar_mercado);

        // Conmutar notificación
        $toggleResponse = $this->actingAs($user)->post(route('configuracion.usuarios.toggle_notification', $user->id));
        $toggleResponse->assertSessionHas('status');
        $user->refresh();
        $this->assertFalse($user->notificar_email);
    }

    public function test_user_can_view_licitacion_raw_json(): void
    {
        $user = User::factory()->create([
            'email' => 'json_test@tornus.agency',
            'activo' => true,
        ]);

        $lic = Licitacion::create([
            'external_id' => 'CO1.REQ.TESTJSON',
            'referencia' => 'PROCESO-JSON-001',
            'entidad' => 'Entidad de Prueba',
            'objeto' => 'Campaña de Promoción y Marca Territorial',
            'cuantia' => 250000000,
            'moneda' => 'COP',
            'tipo_proceso' => 'Licitación Pública',
            'estado' => 'Convocado',
            'fecha_publicacion' => '2026-11-01 10:00:00',
            'fecha_cierre' => '2026-11-20 17:00:00',
        ]);

        $response = $this->actingAs($user)->get("/licitaciones/{$lic->id}/json");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json; charset=utf-8');
        $response->assertJsonFragment([
            'external_id' => 'CO1.REQ.TESTJSON',
            'referencia' => 'PROCESO-JSON-001',
            'fecha_cierre' => '2026-11-20 17:00:00',
        ]);
    }

    public function test_alert_notification_service_only_selects_fresh_proposals_with_ample_window(): void
    {
        // 1. Propuesta ya cerrada (no debe seleccionarse)
        Licitacion::on('colombia')->create([
            'external_id' => 'CO1.REQ.CLOSED',
            'referencia' => 'PROC-CLOSED',
            'entidad' => 'FONTUR',
            'objeto' => 'Campaña turística cerrada',
            'cuantia' => 100000000,
            'moneda' => 'COP',
            'tipo_proceso' => 'Licitación',
            'estado' => 'Cerrado',
            'fecha_publicacion' => now()->subDays(10)->toDateTimeString(),
            'fecha_cierre' => now()->subDay()->toDateTimeString(),
        ]);

        // 2. Propuesta con cierre inminente < 48 horas (no debe seleccionarse)
        Licitacion::on('colombia')->create([
            'external_id' => 'CO1.REQ.IMMINENT',
            'referencia' => 'PROC-IMMINENT',
            'entidad' => 'FONTUR',
            'objeto' => 'Campaña turística por vencer en 24h',
            'cuantia' => 150000000,
            'moneda' => 'COP',
            'tipo_proceso' => 'Licitación',
            'estado' => 'Convocado',
            'fecha_publicacion' => now()->subDays(2)->toDateTimeString(),
            'fecha_cierre' => now()->addHours(24)->toDateTimeString(),
        ]);

        // 3. Propuesta recién salida con ventana amplia de 20 días (DEBE seleccionarse)
        $fresh = Licitacion::on('colombia')->create([
            'external_id' => 'CO1.REQ.FRESH',
            'referencia' => 'PROC-FRESH',
            'entidad' => 'FONDO NACIONAL DEL TURISMO FONTUR',
            'objeto' => 'Estrategia integral de promoción turística nacional y marketing de destinos',
            'cuantia' => 850000000,
            'moneda' => 'COP',
            'tipo_proceso' => 'Licitación Pública',
            'estado' => 'Convocado',
            'fecha_publicacion' => now()->addDays(20)->toDateTimeString(),
            'fecha_cierre' => now()->addDays(40)->toDateTimeString(),
        ]);

        $eligible = \App\Services\AlertNotificationService::getEligibleNewTendersForAlerts('CO', 48, 3, 30);
        $ids = array_map(fn($t) => is_object($t) ? $t->external_id : $t['external_id'], $eligible);

        $this->assertContains('CO1.REQ.FRESH', $ids);
        $this->assertNotContains('CO1.REQ.CLOSED', $ids);
        $this->assertNotContains('CO1.REQ.IMMINENT', $ids);
    }

    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_login_endpoint_has_rate_limiting(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'bad_attempts@tornus.agency',
                'password' => 'wrongpass',
            ]);
        }

        // El 7º intento debe ser bloqueado por HTTP 429 Too Many Requests
        $response = $this->post('/login', [
            'email' => 'bad_attempts@tornus.agency',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(429);
    }
}
