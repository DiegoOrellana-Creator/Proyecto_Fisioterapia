<?php

namespace Tests\Feature;

use App\Models\Bloqueo;
use App\Models\Cita;
use App\Models\Establecimiento;
use App\Models\Horario;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaTest extends TestCase
{
    use RefreshDatabase;

    private Profesional $profesional;
    private Servicio $servicio;
    private Carbon $lunes;

    protected function setUp(): void
    {
        parent::setUp();

        // Fijamos "ahora" un viernes a las 10:00 para que las pruebas no dependan del día real
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));
        $this->lunes = Carbon::parse('2026-11-23');

        $est = Establecimiento::create(['nombre' => 'Centro Prueba', 'slug' => 'centro-prueba', 'direccion' => 'Calle 1', 'zona' => 'Centro']);
        $this->servicio = $est->servicios()->create([
            'nombre' => 'Rehabilitación', 'categoria' => 'Traumatología', 'duracion_min' => 45, 'precio' => 120, 'sesiones_sugeridas' => 4,
        ]);
        $user = User::factory()->rol(User::ROL_PROFESIONAL)->create();
        $this->profesional = Profesional::create(['user_id' => $user->id, 'establecimiento_id' => $est->id, 'especialidad' => 'General']);
        $this->profesional->servicios()->attach($this->servicio);
        Horario::create(['profesional_id' => $this->profesional->id, 'dia_semana' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '12:00:00']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function reservar(User $paciente, string $hora, int $sesiones = 1)
    {
        return $this->actingAs($paciente)->post(route('paciente.reservar', $this->profesional), [
            'servicio_id' => $this->servicio->id,
            'fecha' => $this->lunes->toDateString(),
            'hora' => $hora,
            'sesiones' => $sesiones,
        ]);
    }

    public function test_la_disponibilidad_respeta_horario_y_duracion(): void
    {
        $res = $this->getJson(route('reservar.disponibilidad', [$this->profesional, 'servicio' => $this->servicio->id, 'fecha' => $this->lunes->toDateString()]));

        $res->assertOk();
        $inicios = collect($res->json('horarios'))->pluck('inicio');
        // 08:00 a 12:00 con pasos de 30 min y sesiones de 45 min: la última empieza 11:00
        $this->assertSame(['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00'], $inicios->all());
    }

    public function test_un_dia_sin_atencion_no_ofrece_horarios(): void
    {
        $martes = $this->lunes->copy()->addDay()->toDateString();
        $this->getJson(route('reservar.disponibilidad', [$this->profesional, 'servicio' => $this->servicio->id, 'fecha' => $martes]))
            ->assertOk()->assertJson(['horarios' => []]);
    }

    public function test_el_paciente_reserva_y_la_cita_queda_solicitada(): void
    {
        $paciente = User::factory()->create();

        $this->reservar($paciente, '09:00')->assertSessionHasNoErrors()->assertRedirect();

        $cita = Cita::first();
        $this->assertSame(Cita::SOLICITADA, $cita->estado);
        $this->assertSame('09:00:00', $cita->hora_inicio);
        $this->assertSame('09:45:00', $cita->hora_fin);
        $this->assertSame(1, $cita->historial()->count());
    }

    public function test_no_se_puede_reservar_un_horario_ocupado(): void
    {
        $this->reservar(User::factory()->create(), '09:00')->assertSessionHasNoErrors();

        // Otro paciente intenta 09:30: se cruza con la cita de 09:00 a 09:45
        $this->reservar(User::factory()->create(), '09:30')->assertSessionHasErrors('hora');

        $this->assertSame(1, Cita::count());
    }

    public function test_el_horario_ocupado_desaparece_de_la_disponibilidad(): void
    {
        $this->reservar(User::factory()->create(), '09:00');

        $inicios = collect($this->getJson(route('reservar.disponibilidad', [$this->profesional, 'servicio' => $this->servicio->id, 'fecha' => $this->lunes->toDateString()]))->json('horarios'))->pluck('inicio');

        $this->assertNotContains('08:30', $inicios); // 08:30–09:15 se cruza
        $this->assertNotContains('09:00', $inicios);
        $this->assertNotContains('09:30', $inicios);
        $this->assertContains('10:00', $inicios);
    }

    public function test_un_bloqueo_impide_reservar(): void
    {
        Bloqueo::create(['profesional_id' => $this->profesional->id, 'inicio' => $this->lunes->copy()->setTime(8, 0), 'fin' => $this->lunes->copy()->setTime(10, 0)]);

        $this->reservar(User::factory()->create(), '08:00')->assertSessionHasErrors('hora');
        $this->reservar(User::factory()->create(), '10:00')->assertSessionHasNoErrors();
    }

    public function test_no_se_puede_reservar_fuera_del_horario(): void
    {
        $this->reservar(User::factory()->create(), '11:30')->assertSessionHasErrors('hora'); // terminaría 12:15
    }

    public function test_reserva_de_varias_sesiones_semanales(): void
    {
        $this->reservar(User::factory()->create(), '08:00', 3)->assertSessionHasNoErrors();

        $citas = Cita::orderBy('fecha')->get();
        $this->assertCount(3, $citas);
        $this->assertSame(['2026-11-23', '2026-11-30', '2026-12-07'], $citas->map(fn ($c) => $c->fecha->toDateString())->all());
        $this->assertCount(1, $citas->pluck('grupo')->unique());
    }

    public function test_si_una_sesion_choca_no_se_guarda_ninguna(): void
    {
        // Ocupamos la tercera semana
        Bloqueo::create(['profesional_id' => $this->profesional->id, 'inicio' => '2026-12-07 07:00', 'fin' => '2026-12-07 13:00']);

        $this->reservar(User::factory()->create(), '08:00', 3)->assertSessionHasErrors('hora');
        $this->assertSame(0, Cita::count());
    }

    public function test_cancelar_libera_el_horario(): void
    {
        $paciente = User::factory()->create();
        $this->reservar($paciente, '09:00');
        $cita = Cita::first();

        $this->actingAs($paciente)->post(route('paciente.citas.cancelar', $cita), ['motivo' => 'Viaje'])->assertSessionHasNoErrors();
        $this->assertSame(Cita::CANCELADA, $cita->fresh()->estado);

        $this->reservar(User::factory()->create(), '09:00')->assertSessionHasNoErrors();
    }

    public function test_reprogramar_cambia_el_horario_y_pide_confirmacion(): void
    {
        $paciente = User::factory()->create();
        $this->reservar($paciente, '09:00');
        $cita = Cita::first();
        $cita->cambiarEstado(Cita::CONFIRMADA);

        $this->actingAs($paciente)->post(route('paciente.citas.reprogramar', $cita), [
            'fecha' => $this->lunes->toDateString(), 'hora' => '09:30',
        ])->assertSessionHasNoErrors();

        $cita->refresh();
        $this->assertSame(Cita::REPROGRAMADA, $cita->estado);
        $this->assertSame('09:30:00', $cita->hora_inicio);
    }

    public function test_otro_paciente_no_puede_ver_ni_cancelar_la_cita(): void
    {
        $this->reservar(User::factory()->create(), '09:00');
        $cita = Cita::first();
        $intruso = User::factory()->create();

        $this->actingAs($intruso)->get(route('paciente.citas.show', $cita))->assertForbidden();
        $this->actingAs($intruso)->post(route('paciente.citas.cancelar', $cita), ['motivo' => 'x'])->assertForbidden();
    }

    public function test_el_profesional_confirma_y_registra_asistencia(): void
    {
        $this->reservar(User::factory()->create(), '09:00');
        $cita = Cita::first();
        $pro = $this->profesional->user;

        $this->actingAs($pro)->post(route('gestion.citas.confirmar', $cita))->assertSessionHasNoErrors();
        $this->assertSame(Cita::CONFIRMADA, $cita->fresh()->estado);

        // Antes de la hora no se registra asistencia
        $this->actingAs($pro)->post(route('gestion.citas.asistencia', $cita), ['asistio' => 0])->assertStatus(422);

        Carbon::setTestNow($this->lunes->copy()->setTime(10, 0));
        $this->actingAs($pro)->post(route('gestion.citas.asistencia', $cita), ['asistio' => 0])->assertSessionHasNoErrors();
        $this->assertSame(Cita::NO_ASISTIO, $cita->fresh()->estado);
    }

    public function test_un_profesional_no_gestiona_citas_de_otro(): void
    {
        $this->reservar(User::factory()->create(), '09:00');
        $cita = Cita::first();

        $otroUser = User::factory()->rol(User::ROL_PROFESIONAL)->create();
        Profesional::create(['user_id' => $otroUser->id, 'establecimiento_id' => $this->profesional->establecimiento_id, 'especialidad' => 'Otra']);

        $this->actingAs($otroUser)->post(route('gestion.citas.confirmar', $cita))->assertForbidden();
    }

    public function test_solo_se_valora_una_cita_atendida(): void
    {
        $paciente = User::factory()->create();
        $this->reservar($paciente, '09:00');
        $cita = Cita::first();

        $this->actingAs($paciente)->post(route('paciente.citas.valorar', $cita), ['puntuacion' => 5])->assertStatus(422);
        $this->actingAs($paciente)->get(route('paciente.citas'))->assertDontSee('Calificar y comentar');

        $cita->cambiarEstado(Cita::ATENDIDA);
        $this->actingAs($paciente)->get(route('paciente.citas'))->assertSee('Calificar y comentar');
        $this->actingAs($paciente)->get(route('paciente.citas.show', $cita))->assertSee('¿Cómo fue la atención?', false);
        $this->actingAs($paciente)->post(route('paciente.citas.valorar', $cita), ['puntuacion' => 5, 'comentario' => 'Muy bien'])->assertSessionHasNoErrors();
        $this->assertSame(5, $cita->fresh()->valoracion->puntuacion);
    }

    public function test_las_paginas_publicas_cargan(): void
    {
        $this->get('/')->assertOk()->assertSee('Próximos horarios libres');
        $this->get(route('establecimientos.index'))->assertOk()->assertSee('Centro Prueba');
        $this->get(route('establecimientos.show', 'centro-prueba'))->assertOk()->assertSee('Rehabilitación');
        $this->get(route('profesionales.show', $this->profesional))->assertOk();
        $this->get(route('servicios.index'))->assertOk()->assertSee('Bs 120,00');
        $this->get(route('reservar.create', $this->profesional))->assertOk()->assertSee('necesitas una cuenta de paciente');
    }

    public function test_el_catalogo_muestra_disponibilidad_y_acepta_orden(): void
    {
        foreach (['valoracion', 'precio', 'nombre'] as $orden) {
            $this->get(route('establecimientos.index', ['orden' => $orden]))->assertOk();
        }
        // "Ahora" es viernes; el profesional atiende lunes, así que el próximo libre es el lunes
        $this->get(route('establecimientos.index'))->assertSee('Próximo libre: lunes 23', false);
        $this->get(route('establecimientos.index', ['categoria' => 'Pediátrica']))->assertSee('Ningún establecimiento coincide');
        $this->get(route('guia'))->assertOk()->assertSee('Tokens de interfaz');
    }

    public function test_un_visitante_pide_un_establecimiento_que_no_esta(): void
    {
        $this->from(route('establecimientos.index'))
            ->post(route('sugerencias.store'), ['establecimiento' => 'Kine Sur', 'zona' => 'Sur'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('sugerencia');

        $this->assertDatabaseHas('sugerencias', ['establecimiento' => 'Kine Sur', 'revisada' => false]);
        $this->post(route('sugerencias.store'), [])->assertSessionHasErrors('establecimiento');
    }

    public function test_las_paginas_de_error_usan_la_marca(): void
    {
        $this->get('/establecimientos/no-existe')->assertNotFound()->assertSee('No encontramos esta página');
    }

    public function test_cada_rol_entra_solo_a_su_seccion(): void
    {
        $paciente = User::factory()->create();
        $admin = User::factory()->rol(User::ROL_ADMIN)->create();

        $this->actingAs($paciente)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($paciente)->get(route('gestion.agenda'))->assertForbidden();
        $this->actingAs($this->profesional->user)->get(route('gestion.agenda'))->assertOk();
        $this->actingAs($this->profesional->user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk();
    }
}
