<x-app-layout titulo="Reprogramar cita">
    <x-slot name="header">
        <a href="{{ route('paciente.citas.show', $cita) }}" class="text-sm font-medium text-petroleo hover:underline">Volver a la cita</a>
        <h1 class="titulo-l mt-2">Reprogramar cita</h1>
        <p class="mt-1 text-[15px] text-tinta-suave">
            {{ $cita->servicio->nombre }} con {{ $cita->profesional->user->nombre_completo }}.
            Ahora: <span class="dato font-semibold text-tinta">{{ $cita->fecha->translatedFormat('l j \d\e F') }}, {{ substr($cita->hora_inicio, 0, 5) }}</span>.
        </p>
    </x-slot>

    <form method="POST" action="{{ route('paciente.citas.reprogramar', $cita) }}" class="tarjeta max-w-3xl p-5 sm:p-6"
          x-data="selectorHorario({
                url: @js(route('reservar.disponibilidad', $cita->profesional)),
                servicio: {{ $cita->servicio_id }},
                fecha: @js(old('fecha', $cita->fecha->toDateString())),
                ignorar: {{ $cita->id }},
                dias: @js($diasAtencion),
                max: {{ $maxDias }},
          })">
        @csrf
        <x-selector-horario />
        <x-input-error :messages="$errors->get('hora')" />

        <div class="mt-6 flex flex-wrap gap-3 border-t border-linea pt-5">
            <button class="btn-primario" :disabled="!hora">Cambiar a <span class="dato" x-text="hora ?? '…'"></span></button>
            <a href="{{ route('paciente.citas.show', $cita) }}" class="btn-texto">Mantener el horario actual</a>
        </div>
    </form>
</x-app-layout>
