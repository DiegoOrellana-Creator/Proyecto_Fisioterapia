<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Gestion;
use App\Http\Controllers\Paciente;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SitioController;
use Illuminate\Support\Facades\Route;

// ── Sitio público ─────────────────────────────────────────────
Route::get('/', [SitioController::class, 'inicio'])->name('inicio');
Route::get('/establecimientos', [SitioController::class, 'establecimientos'])->name('establecimientos.index');
Route::get('/establecimientos/{establecimiento}', [SitioController::class, 'establecimiento'])->name('establecimientos.show');
Route::get('/profesionales/{profesional}', [SitioController::class, 'profesional'])->name('profesionales.show');
Route::get('/servicios', [SitioController::class, 'servicios'])->name('servicios.index');
Route::get('/como-funciona', [SitioController::class, 'comoFunciona'])->name('como-funciona');
Route::post('/sugerencias', [SitioController::class, 'sugerir'])->middleware('throttle:5,1')->name('sugerencias.store');
Route::view('/guia-de-estilo', 'sitio.guia')->name('guia');

// Reserva: el formulario es público; confirmar exige sesión de paciente
Route::get('/reservar/{profesional}', [ReservaController::class, 'create'])->name('reservar.create');
Route::get('/disponibilidad/{profesional}', [ReservaController::class, 'disponibilidad'])->name('reservar.disponibilidad');

Route::middleware('auth')->group(function () {
    // Después de iniciar sesión, cada rol va a su sección
    Route::get('/panel', fn () => redirect(auth()->user()->rutaInicio()))->name('dashboard');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/perfil/profesional', [ProfileController::class, 'updateProfesional'])->name('profile.profesional');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Paciente ──────────────────────────────────────────────
    Route::middleware('rol:paciente')->prefix('mis-citas')->name('paciente.')->group(function () {
        Route::post('/reservar/{profesional}', [ReservaController::class, 'store'])->name('reservar');
        Route::get('/', [Paciente\CitaController::class, 'index'])->name('citas');
        Route::get('/{cita}', [Paciente\CitaController::class, 'show'])->name('citas.show');
        Route::post('/{cita}/cancelar', [Paciente\CitaController::class, 'cancelar'])->name('citas.cancelar');
        Route::get('/{cita}/reprogramar', [Paciente\CitaController::class, 'reprogramarForm'])->name('citas.reprogramar');
        Route::post('/{cita}/reprogramar', [Paciente\CitaController::class, 'reprogramar']);
        Route::post('/{cita}/valorar', [Paciente\CitaController::class, 'valorar'])->name('citas.valorar');
    });

    // ── Profesional y administrador: agenda, citas y horarios ──
    Route::middleware('rol:profesional,admin')->prefix('gestion')->name('gestion.')->group(function () {
        Route::get('/agenda', [Gestion\CitaController::class, 'agenda'])->name('agenda');
        Route::get('/citas', [Gestion\CitaController::class, 'index'])->name('citas');
        Route::get('/citas/{cita}', [Gestion\CitaController::class, 'show'])->name('citas.show');
        Route::post('/citas/{cita}/confirmar', [Gestion\CitaController::class, 'confirmar'])->name('citas.confirmar');
        Route::post('/citas/{cita}/cancelar', [Gestion\CitaController::class, 'cancelar'])->name('citas.cancelar');
        Route::post('/citas/{cita}/asistencia', [Gestion\CitaController::class, 'asistencia'])->name('citas.asistencia');
        Route::patch('/citas/{cita}/nota', [Gestion\CitaController::class, 'nota'])->name('citas.nota');
        Route::get('/pacientes/{paciente}', [Gestion\CitaController::class, 'paciente'])->name('pacientes.show');

        Route::get('/mis-horarios', [Gestion\HorarioController::class, 'propios'])->name('horarios.propios');
        Route::get('/profesionales/{profesional}/horarios', [Gestion\HorarioController::class, 'index'])->name('horarios');
        Route::post('/profesionales/{profesional}/horarios', [Gestion\HorarioController::class, 'store'])->name('horarios.store');
        Route::delete('/profesionales/{profesional}/horarios/{horario}', [Gestion\HorarioController::class, 'destroy'])->name('horarios.destroy');
        Route::post('/profesionales/{profesional}/bloqueos', [Gestion\HorarioController::class, 'storeBloqueo'])->name('bloqueos.store');
        Route::delete('/profesionales/{profesional}/bloqueos/{bloqueo}', [Gestion\HorarioController::class, 'destroyBloqueo'])->name('bloqueos.destroy');
    });

    Route::get('/profesional', fn () => redirect()->route('gestion.agenda'))->middleware('rol:profesional')->name('profesional.agenda');

    // ── Administración ────────────────────────────────────────
    Route::middleware('rol:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::resource('establecimientos', Admin\EstablecimientoController::class)->except(['show', 'destroy']);
        Route::resource('servicios', Admin\ServicioController::class)->except(['show', 'destroy']);
        Route::resource('profesionales', Admin\ProfesionalController::class)
            ->parameters(['profesionales' => 'profesional'])
            ->except(['show', 'destroy']);

        Route::middleware('admin.general')->group(function () {
            Route::get('/usuarios', [Admin\UsuarioController::class, 'index'])->name('usuarios.index');
            Route::get('/usuarios/nuevo-administrador', [Admin\UsuarioController::class, 'create'])->name('usuarios.create');
            Route::post('/usuarios', [Admin\UsuarioController::class, 'store'])->name('usuarios.store');
            Route::patch('/usuarios/{usuario}/estado', [Admin\UsuarioController::class, 'toggle'])->name('usuarios.toggle');
            Route::patch('/sugerencias/{sugerencia}', function (\App\Models\Sugerencia $sugerencia) {
                $sugerencia->update(['revisada' => true]);

                return back()->with('ok', 'Pedido marcado como revisado.');
            })->name('sugerencias.revisar');
        });
    });
});

require __DIR__.'/auth.php';
