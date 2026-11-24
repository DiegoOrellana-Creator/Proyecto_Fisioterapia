<x-layouts.sitio titulo="Cómo reservar">
    <div class="mx-auto max-w-3xl px-5 pt-12">
        <h1 class="titulo-l">Cómo funciona una reserva</h1>
        <p class="mt-3 text-lg leading-relaxed text-tinta-suave">La plataforma reemplaza las llamadas y los mensajes por una agenda compartida: el paciente ve los mismos horarios libres que maneja el establecimiento.</p>

        <h2 class="titulo-m mt-12">Para pacientes</h2>
        <ol class="mt-5 space-y-6">
            @foreach ([
                ['Busca', 'Filtra establecimientos por zona o especialidad y compara precios, duración de cada sesión y opiniones de otros pacientes.'],
                ['Elige profesional, día y hora', 'Solo se muestran horarios realmente libres: el sistema descuenta citas reservadas, descansos y vacaciones del profesional.'],
                ['Reserva una o varias sesiones', 'Si tu tratamiento lleva varias sesiones, puedes reservarlas a la misma hora en semanas seguidas. Si alguna ya no está libre, te avisamos antes de guardar.'],
                ['Espera la confirmación', 'Tu cita queda «Solicitada» hasta que el establecimiento la confirma. Ves el estado en «Mis citas».'],
                ['Cancela o reprograma si lo necesitas', 'Desde tu cuenta, hasta antes de la hora de la cita. El horario se libera para otro paciente.'],
                ['Opina después de tu sesión', 'Cuando el profesional registra tu asistencia, puedes valorar la atención de 1 a 5 estrellas.'],
            ] as $i => [$t, $d])
                <li class="grid grid-cols-[40px_1fr] gap-4">
                    <span class="num flex h-10 w-10 items-center justify-center rounded-full bg-petroleo-claro font-bold text-petroleo-oscuro">{{ $i + 1 }}</span>
                    <div>
                        <p class="font-semibold">{{ $t }}</p>
                        <p class="mt-1 leading-relaxed text-tinta-suave">{{ $d }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <h2 class="titulo-m mt-14">Estados de una cita</h2>
        <div class="tarjeta mt-5 divide-y divide-linea">
            @foreach ([
                'solicitada' => 'El paciente reservó; falta que el establecimiento confirme.',
                'confirmada' => 'El establecimiento aceptó la cita.',
                'reprogramada' => 'El paciente cambió la fecha u hora; falta confirmar el nuevo horario.',
                'cancelada' => 'La cancelaron el paciente o el establecimiento. El horario quedó libre.',
                'atendida' => 'El paciente asistió.',
                'no_asistio' => 'El paciente no se presentó. Queda en su historial de asistencia.',
            ] as $estado => $texto)
                <div class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:gap-5">
                    <x-estado :estado="$estado" class="w-fit sm:w-36 sm:justify-start" />
                    <p class="text-[15px] text-tinta-suave">{{ $texto }}</p>
                </div>
            @endforeach
        </div>

        <h2 id="establecimientos" class="titulo-m mt-14">Para establecimientos y profesionales</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['Perfil del establecimiento', 'Dirección, zona, contacto, servicios con precio, duración y sesiones sugeridas.'],
                ['Horarios y bloqueos', 'Cada profesional define sus días y horas de atención, y bloquea vacaciones o ausencias.'],
                ['Agenda semanal', 'Citas por confirmar, citas del día y citas pasadas pendientes de registrar asistencia.'],
                ['Historial de pacientes', 'Asistencias, inasistencias y cancelaciones de cada paciente, visibles solo para su establecimiento.'],
            ] as [$t, $d])
                <li class="tarjeta p-5">
                    <p class="font-semibold">{{ $t }}</p>
                    <p class="mt-1 text-[15px] leading-relaxed text-tinta-suave">{{ $d }}</p>
                </li>
            @endforeach
        </ul>

        <div class="mt-12 flex flex-wrap gap-3">
            <a href="{{ route('establecimientos.index') }}" class="btn-primario">Buscar establecimiento</a>
            @guest<a href="{{ route('register') }}" class="btn-secundario">Crear cuenta de paciente</a>@endguest
        </div>
    </div>
</x-layouts.sitio>
