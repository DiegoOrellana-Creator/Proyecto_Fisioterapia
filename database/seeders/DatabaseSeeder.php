<?php

namespace Database\Seeders;

use App\Models\Bloqueo;
use App\Models\Cita;
use App\Models\Establecimiento;
use App\Models\Horario;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Valoracion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Datos de demostración del prototipo. Los establecimientos y personas son ficticios.
 *
 * Cuentas de prueba (contraseña de todas: la variable DEMO_PASSWORD, o "password" si no existe):
 *   admin@fisioagenda.test             Administrador general
 *   admin.equipetrol@fisioagenda.test  Administrador del Centro Kinésico Equipetrol
 *   lucia.rojas@fisioagenda.test       Fisioterapeuta
 *   paciente@fisioagenda.test          Paciente con historial
 */
class DatabaseSeeder extends Seeder
{
    private string $clave;

    public function run(): void
    {
        // En producción define DEMO_PASSWORD para no publicar la contraseña de ejemplo
        $this->clave = Hash::make(config('app.demo_password'));

        $establecimientos = $this->establecimientos();
        $profesionales = $this->profesionales($establecimientos);

        $this->usuario('Administración', 'General', 'admin@fisioagenda.test', User::ROL_ADMIN);
        $this->usuario('Carla', 'Suárez', 'admin.equipetrol@fisioagenda.test', User::ROL_ADMIN, [
            'establecimiento_id' => $establecimientos['equipetrol']->id,
        ]);

        $pacientes = collect([
            $this->usuario('Mariana', 'Gutiérrez', 'paciente@fisioagenda.test', User::ROL_PACIENTE, ['ci' => '7812345', 'telefono' => '76012345']),
            $this->usuario('Jorge', 'Áñez', 'jorge.anez@correo.test', User::ROL_PACIENTE, ['telefono' => '71234567']),
            $this->usuario('Daniela', 'Vaca', 'daniela.vaca@correo.test', User::ROL_PACIENTE, ['telefono' => '77654321']),
            $this->usuario('Rodrigo', 'Saucedo', 'rodrigo.saucedo@correo.test', User::ROL_PACIENTE, ['telefono' => '70011223']),
            $this->usuario('Paola', 'Justiniano', 'paola.justiniano@correo.test', User::ROL_PACIENTE, ['telefono' => '78899001']),
            $this->usuario('Fernando', 'Roca', 'fernando.roca@correo.test', User::ROL_PACIENTE, ['telefono' => '75566778']),
        ]);

        $this->citas($profesionales, $pacientes);
    }

    private function usuario(string $nombre, string $apellido, string $email, string $rol, array $extra = []): User
    {
        return User::create(array_merge([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'password' => $this->clave,
            'email_verified_at' => now(),
            'rol' => $rol,
            'telefono' => '7'.random_int(1000000, 9999999),
        ], $extra));
    }

    /** @return array<string, Establecimiento> */
    private function establecimientos(): array
    {
        $datos = [
            'equipetrol' => [
                'nombre' => 'Centro Kinésico Equipetrol',
                'zona' => 'Equipetrol',
                'direccion' => 'Calle 9 Oeste #21, entre Av. San Martín y Calle Los Cusis',
                'telefono' => '3 3412233', 'whatsapp' => '70812233',
                'horario_texto' => 'Lun a vie 7:00–20:00 · Sáb 8:00–12:00',
                'descripcion' => 'Centro con cinco boxes de atención, gimnasio de rehabilitación y equipos de electroterapia. Atendemos lesiones deportivas, posoperatorios de rodilla y hombro, y dolor de espalda.',
                'servicios' => [
                    ['Evaluación fisioterapéutica inicial', 'Evaluación', 40, 120, 1, 'Entrevista, pruebas de movilidad y fuerza, y plan de tratamiento por escrito.'],
                    ['Rehabilitación de rodilla (LCA, menisco)', 'Traumatología', 50, 150, 10, 'Ejercicio terapéutico progresivo después de cirugía o lesión de ligamentos y meniscos.'],
                    ['Fisioterapia deportiva', 'Deportiva', 45, 140, 6, 'Tratamiento de esguinces, desgarros y tendinopatías, con retorno guiado al deporte.'],
                    ['Terapia manual de columna', 'Terapia manual', 45, 130, 5, 'Movilizaciones y técnicas miofasciales para dolor lumbar y cervical.'],
                    ['Rehabilitación de hombro', 'Traumatología', 45, 140, 8, 'Manguito rotador, hombro congelado y posoperatorio de hombro.'],
                ],
            ],
            'norte' => [
                'nombre' => 'Fisiomovimiento Norte',
                'zona' => 'Norte',
                'direccion' => 'Av. Banzer, 4.º anillo, Edif. Torres del Norte, planta baja',
                'telefono' => '3 3445566', 'whatsapp' => '72145566',
                'horario_texto' => 'Lun a sáb 8:00–19:00',
                'descripcion' => 'Atención para adultos mayores y pacientes neurológicos, con sesiones adaptadas y acceso sin escaleras.',
                'servicios' => [
                    ['Evaluación fisioterapéutica inicial', 'Evaluación', 30, 100, 1, 'Valoración general y propuesta de número de sesiones.'],
                    ['Rehabilitación neurológica (ACV)', 'Neurológica', 60, 160, 12, 'Recuperación de la marcha, el equilibrio y la función de brazo y mano después de un ACV.'],
                    ['Fisioterapia geriátrica y prevención de caídas', 'Geriátrica', 45, 120, 8, 'Fuerza, equilibrio y entrenamiento de la marcha para adultos mayores.'],
                    ['Fisioterapia respiratoria', 'Respiratoria', 40, 110, 6, 'Ejercicios respiratorios y de tolerancia al esfuerzo, incluida la recuperación pos-COVID.'],
                ],
            ],
            'urbari' => [
                'nombre' => 'Rehabilita Urbarí',
                'zona' => 'Urbarí',
                'direccion' => 'Av. Roca y Coronado #540, frente al parque Urbarí',
                'telefono' => '3 3529988', 'whatsapp' => '76529988',
                'horario_texto' => 'Lun a vie 8:00–18:30',
                'descripcion' => 'Fisioterapia pediátrica y de traumatología en un ambiente pensado para familias.',
                'servicios' => [
                    ['Evaluación fisioterapéutica inicial', 'Evaluación', 30, 90, 1, 'Primera consulta y plan de tratamiento.'],
                    ['Fisioterapia pediátrica', 'Pediátrica', 45, 130, 10, 'Desarrollo motor, tortícolis congénita y pie plano en niños.'],
                    ['Rehabilitación de tobillo y pie', 'Traumatología', 40, 110, 6, 'Esguinces de tobillo, fascitis plantar y posoperatorios de pie.'],
                    ['Terapia manual cervical', 'Terapia manual', 40, 110, 4, 'Dolor de cuello, cefalea tensional y rigidez cervical.'],
                ],
            ],
            'plan3000' => [
                'nombre' => 'Consultorio Fisio Plan 3000',
                'zona' => 'Plan 3000',
                'direccion' => 'Av. Paurito, a media cuadra de la rotonda del Plan 3000',
                'telefono' => null, 'whatsapp' => '69031122',
                'horario_texto' => 'Lun a sáb 14:00–20:00',
                'descripcion' => 'Consultorio de una fisioterapeuta con tarifas accesibles. Atención por la tarde.',
                'servicios' => [
                    ['Evaluación fisioterapéutica inicial', 'Evaluación', 30, 70, 1, 'Primera consulta.'],
                    ['Fisioterapia de espalda baja', 'Terapia manual', 45, 90, 6, 'Lumbalgia, ciática y dolor por postura en el trabajo.'],
                    ['Rehabilitación posfractura', 'Traumatología', 45, 90, 8, 'Recuperación de movilidad y fuerza después de retirar el yeso.'],
                ],
            ],
            'laspalmas' => [
                'nombre' => 'Espacio Pélvico Las Palmas',
                'zona' => 'Las Palmas',
                'direccion' => 'Calle Los Tajibos #18, barrio Las Palmas',
                'telefono' => '3 3557788', 'whatsapp' => '73557788',
                'horario_texto' => 'Lun a vie 9:00–18:00',
                'descripcion' => 'Fisioterapia de suelo pélvico para embarazo, posparto e incontinencia, en consultorios privados.',
                'servicios' => [
                    ['Evaluación de suelo pélvico', 'Suelo pélvico', 60, 180, 1, 'Valoración funcional del suelo pélvico con consentimiento informado.'],
                    ['Rehabilitación posparto', 'Suelo pélvico', 50, 160, 8, 'Diástasis abdominal, fuerza del suelo pélvico y retorno al ejercicio.'],
                    ['Tratamiento de incontinencia urinaria', 'Suelo pélvico', 45, 150, 8, 'Ejercicio guiado y educación para mujeres y hombres.'],
                ],
            ],
        ];

        $resultado = [];
        foreach ($datos as $clave => $d) {
            $servicios = $d['servicios'];
            unset($d['servicios']);

            $est = Establecimiento::create($d + ['slug' => Str::slug($d['nombre']), 'email' => $clave.'@fisioagenda.test']);

            foreach ($servicios as [$nombre, $categoria, $duracion, $precio, $sesiones, $descripcion]) {
                $est->servicios()->create([
                    'nombre' => $nombre, 'categoria' => $categoria, 'duracion_min' => $duracion,
                    'precio' => $precio, 'sesiones_sugeridas' => $sesiones, 'descripcion' => $descripcion,
                ]);
            }
            $resultado[$clave] = $est;
        }

        return $resultado;
    }

    /** @return array<int, Profesional> */
    private function profesionales(array $est): array
    {
        $manana = [['08:00', '12:00'], ['14:30', '19:00']];
        $semana = [1, 2, 3, 4, 5];

        $datos = [
            ['Lucía', 'Rojas', 'lucia.rojas@fisioagenda.test', 'equipetrol', 'Fisioterapia deportiva y traumatológica', 9, 'Trabajo con deportistas amateurs y de liga. Me enfoco en que vuelvas a entrenar sin dolor y con un plan claro de ejercicios para casa.', $semana, $manana, null],
            ['Andrés', 'Méndez', 'andres.mendez@fisioagenda.test', 'equipetrol', 'Rehabilitación posquirúrgica', 12, 'Rehabilitación de rodilla y hombro después de cirugía, coordinando con el traumatólogo.', [1, 3, 5, 6], [['07:00', '13:00']], null],
            ['Valeria', 'Cuéllar', 'valeria.cuellar@fisioagenda.test', 'equipetrol', 'Terapia manual y columna', 6, 'Terapia manual para dolor de espalda y cuello, con educación postural.', [2, 4], [['14:00', '20:00']], null],
            ['Ricardo', 'Peña', 'ricardo.pena@fisioagenda.test', 'norte', 'Fisioterapia neurológica', 15, 'Rehabilitación de pacientes con ACV, Parkinson y lesiones medulares.', $semana, [['08:00', '13:00']], null],
            ['Gabriela', 'Montaño', 'gabriela.montano@fisioagenda.test', 'norte', 'Geriatría y fisioterapia respiratoria', 8, 'Acompaño a adultos mayores a recuperar fuerza y confianza al caminar.', [1, 2, 3, 4, 5, 6], [['14:00', '19:00']], null],
            ['Natalia', 'Ribera', 'natalia.ribera@fisioagenda.test', 'urbari', 'Fisioterapia pediátrica', 7, 'Atención de bebés y niños con juegos y ejercicios que los padres pueden continuar en casa.', $semana, [['08:00', '12:30']], null],
            ['Martín', 'Flores', 'martin.flores@fisioagenda.test', 'urbari', 'Traumatología y terapia manual', 5, 'Esguinces, fascitis plantar y dolor cervical.', $semana, [['13:30', '18:30']], null],
            ['Silvia', 'Choque', 'silvia.choque@fisioagenda.test', 'plan3000', 'Fisioterapia general', 10, 'Consultorio de barrio con atención cercana y precios accesibles.', [1, 2, 3, 4, 5, 6], [['14:00', '20:00']], null],
            ['Camila', 'Terrazas', 'camila.terrazas@fisioagenda.test', 'laspalmas', 'Fisioterapia de suelo pélvico', 8, 'Especialista en salud de la mujer: embarazo, posparto e incontinencia.', $semana, [['09:00', '13:00'], ['15:00', '18:00']], null],
        ];

        $resultado = [];
        foreach ($datos as $i => [$nombre, $apellido, $email, $estClave, $especialidad, $anios, $bio, $dias, $rangos]) {
            $user = $this->usuario($nombre, $apellido, $email, User::ROL_PROFESIONAL);
            $establecimiento = $est[$estClave];

            $p = Profesional::create([
                'user_id' => $user->id,
                'establecimiento_id' => $establecimiento->id,
                'matricula' => 'MP-'.(4100 + $i * 37),
                'especialidad' => $especialidad,
                'anios_experiencia' => $anios,
                'biografia' => $bio,
            ]);

            // Cada profesional ofrece la evaluación y los servicios relacionados con su especialidad
            $servicios = $establecimiento->servicios;
            $propios = $servicios->filter(function ($s) use ($especialidad, $servicios) {
                if ($s->categoria === 'Evaluación' || $servicios->count() <= 4) {
                    return true;
                }
                $mapa = [
                    'deportiva' => ['Deportiva', 'Traumatología'],
                    'posquirúrgica' => ['Traumatología'],
                    'columna' => ['Terapia manual'],
                ];
                foreach ($mapa as $palabra => $cats) {
                    if (str_contains(mb_strtolower($especialidad), $palabra)) {
                        return in_array($s->categoria, $cats, true);
                    }
                }

                return true;
            });
            $p->servicios()->sync($propios->pluck('id'));

            foreach ($dias as $dia) {
                foreach ($rangos as [$desde, $hasta]) {
                    Horario::create([
                        'profesional_id' => $p->id, 'dia_semana' => $dia,
                        'hora_inicio' => $desde.':00', 'hora_fin' => $hasta.':00',
                    ]);
                }
            }

            $resultado[] = $p;
        }

        // Ejemplo de bloqueo: Andrés en congreso el próximo viernes
        $viernes = now()->next(Carbon::FRIDAY);
        Bloqueo::create([
            'profesional_id' => $resultado[1]->id,
            'inicio' => $viernes->copy()->setTime(7, 0),
            'fin' => $viernes->copy()->setTime(13, 0),
            'motivo' => 'Congreso de fisioterapia',
        ]);

        return $resultado;
    }

    private function citas(array $profesionales, $pacientes): void
    {
        $comentarios = [
            5 => ['Muy puntual y me explicó cada ejercicio.', 'Excelente atención, ya puedo correr de nuevo.', 'El consultorio es cómodo y limpio.'],
            4 => ['Buena sesión, aunque esperé unos minutos.', 'Me ayudó mucho con el dolor de espalda.'],
            3 => ['La atención fue correcta, pero me cambiaron la hora dos veces.'],
        ];

        $n = 0;
        foreach ($profesionales as $pi => $profesional) {
            $servicios = $profesional->servicios()->get();
            $horarios = $profesional->horarios()->get();
            if ($servicios->isEmpty() || $horarios->isEmpty()) {
                continue;
            }

            // Citas pasadas: atendidas, no asistidas y canceladas
            for ($k = 1; $k <= 6; $k++) {
                $fecha = $this->diaConHorario($horarios, now()->subDays($k * 3 + $pi));
                $h = $horarios->firstWhere('dia_semana', $fecha->isoWeekday());
                $servicio = $servicios[$k % $servicios->count()];
                $inicio = Carbon::parse($fecha->toDateString().' '.$h->hora_inicio)->addMinutes(($k % 3) * 60);
                $paciente = $pacientes[($pi + $k) % $pacientes->count()];

                $estado = match (true) {
                    $k % 5 === 0 => Cita::NO_ASISTIO,
                    $k === 4 && $pi % 2 === 0 => Cita::CANCELADA,
                    default => Cita::ATENDIDA,
                };

                $cita = $this->crearCita($paciente, $profesional, $servicio, $inicio, $estado, ++$n);

                if ($estado === Cita::ATENDIDA && $k <= 3) {
                    $puntaje = [5, 5, 4, 5, 3, 4][($pi + $k) % 6];
                    $lista = $comentarios[$puntaje];
                    Valoracion::create([
                        'cita_id' => $cita->id,
                        'paciente_id' => $paciente->id,
                        'profesional_id' => $profesional->id,
                        'establecimiento_id' => $profesional->establecimiento_id,
                        'puntuacion' => $puntaje,
                        'comentario' => $lista[($pi + $k) % count($lista)],
                    ]);
                }
            }

            // Citas próximas: solicitadas y confirmadas
            for ($k = 1; $k <= 3; $k++) {
                $fecha = $this->diaConHorario($horarios, now()->addDays($k * 2 + ($pi % 2)), true);
                $h = $horarios->firstWhere('dia_semana', $fecha->isoWeekday());
                $servicio = $servicios[$k % $servicios->count()];
                $inicio = Carbon::parse($fecha->toDateString().' '.$h->hora_inicio)->addMinutes($k * 60);
                $paciente = $pacientes[($pi + $k) % $pacientes->count()];

                $this->crearCita($paciente, $profesional, $servicio, $inicio, $k === 1 ? Cita::SOLICITADA : Cita::CONFIRMADA, ++$n);
            }
        }
    }

    private function diaConHorario($horarios, Carbon $desde, bool $haciaAdelante = false): Carbon
    {
        $dias = $horarios->pluck('dia_semana')->unique();
        $fecha = $desde->copy()->startOfDay();
        while (! $dias->contains($fecha->isoWeekday())) {
            $haciaAdelante ? $fecha->addDay() : $fecha->subDay();
        }

        return $fecha;
    }

    private function crearCita(User $paciente, Profesional $profesional, Servicio $servicio, Carbon $inicio, string $estado, int $n): Cita
    {
        $fin = $inicio->copy()->addMinutes($servicio->duracion_min);

        $cita = Cita::create([
            'codigo' => 'FA-'.str_pad((string) (100000 + $n * 7), 6, '0', STR_PAD_LEFT),
            'paciente_id' => $paciente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'establecimiento_id' => $profesional->establecimiento_id,
            'fecha' => $inicio->toDateString(),
            'hora_inicio' => $inicio->format('H:i:s'),
            'hora_fin' => $fin->format('H:i:s'),
            'estado' => $estado,
            'precio' => $servicio->precio,
            'motivo_cancelacion' => $estado === Cita::CANCELADA ? 'Viaje imprevisto' : null,
        ]);

        $cita->historial()->create([
            'user_id' => $paciente->id, 'estado_anterior' => null,
            'estado_nuevo' => Cita::SOLICITADA, 'detalle' => 'Reserva registrada por el paciente',
        ]);
        if ($estado !== Cita::SOLICITADA) {
            $cita->historial()->create([
                'user_id' => $profesional->user_id, 'estado_anterior' => Cita::SOLICITADA,
                'estado_nuevo' => $estado, 'detalle' => null,
            ]);
        }

        return $cita;
    }
}
