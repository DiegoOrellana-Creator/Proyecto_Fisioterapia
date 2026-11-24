<x-app-layout :titulo="'Cita '.$cita->codigo">
    <x-slot name="header">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('gestion.agenda') }}" class="text-sm font-medium text-petroleo hover:underline">Volver</a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <h1 class="titulo-l">{{ $cita->paciente->nombre_completo }}</h1>
            <x-estado :estado="$cita->estado" />
        </div>
        <p class="dato mt-1 text-[15px] text-tinta-suave">{{ $cita->servicio->nombre }}, código {{ $cita->codigo }}</p>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="min-w-0 space-y-6">
            <section class="tarjeta p-5 sm:p-6">
                <dl class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm text-tinta-suave">Fecha</dt>
                        <dd class="font-semibold first-letter:uppercase">{{ $cita->fecha->translatedFormat('l j \d\e F') }}</dd>
                        <dd class="dato font-semibold text-petroleo">{{ $cita->rango_hora }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-tinta-suave">Profesional</dt>
                        <dd class="font-semibold">{{ $cita->profesional->user->nombre_completo }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-tinta-suave">Sesión</dt>
                        <dd class="num font-semibold">{{ $cita->numero_sesion }} de {{ $cita->total_sesiones }}</dd>
                        <dd class="dato text-sm text-tinta-suave">Bs {{ number_format($cita->precio, 2, ',', '.') }}</dd>
                    </div>
                </dl>
                @if ($cita->nota_paciente)
                    <div class="mt-5 rounded-ctl bg-lienzo p-4">
                        <p class="text-sm font-semibold">Comentario del paciente</p>
                        <p class="mt-1 text-[15px]">{{ $cita->nota_paciente }}</p>
                    </div>
                @endif
                @if ($cita->motivo_cancelacion && $cita->estado === 'cancelada')
                    <div class="mt-5 rounded-ctl border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-semibold">Motivo de cancelación</p>
                        <p class="mt-1 text-[15px]">{{ $cita->motivo_cancelacion }}</p>
                    </div>
                @endif

                <div class="mt-6 border-t border-linea pt-5">
                    @include('gestion._acciones', ['c' => $cita])
                </div>
            </section>

            <section class="tarjeta p-5 sm:p-6">
                <h2 class="titulo-m">Nota del profesional</h2>
                <p class="mt-1 text-sm text-tinta-suave">Solo la ven el profesional y el establecimiento.</p>
                <form method="POST" action="{{ route('gestion.citas.nota', $cita) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('patch')
                    <textarea name="nota_profesional" rows="3" class="campo" placeholder="ej. Disminuyó el dolor; continuar con fortalecimiento de cuádriceps.">{{ old('nota_profesional', $cita->nota_profesional) }}</textarea>
                    <button class="btn-secundario btn-sm">Guardar nota</button>
                </form>
            </section>

            @if ($cita->valoracion)
                <section class="tarjeta p-5 sm:p-6">
                    <h2 class="titulo-m">Valoración del paciente</h2>
                    <x-estrellas class="mt-3" :valor="$cita->valoracion->puntuacion" />
                    @if ($cita->valoracion->comentario)<p class="mt-2 text-[15px]">{{ $cita->valoracion->comentario }}</p>@endif
                </section>
            @endif

            <section>
                <h2 class="titulo-m">Historial de cambios</h2>
                <ol class="mt-4 space-y-3 border-l-2 border-linea pl-5">
                    @foreach ($cita->historial as $h)
                        <li class="relative">
                            <span class="absolute -left-[27px] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-petroleo" aria-hidden="true"></span>
                            <p class="text-[15px]"><span class="font-semibold">{{ \App\Models\Cita::ETIQUETAS[$h->estado_nuevo] ?? $h->estado_nuevo }}</span>@if ($h->detalle)<span class="text-tinta-suave">: {{ $h->detalle }}</span>@endif</p>
                            <p class="num text-sm text-tinta-suave">{{ $h->created_at->format('d/m/Y H:i') }}@if ($h->user), {{ $h->user->nombre_completo }}@endif</p>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-8 lg:self-start">
            <div class="tarjeta p-5">
                <h2 class="font-semibold">Paciente</h2>
                <dl class="mt-3 space-y-2 text-[15px]">
                    @if ($cita->paciente->telefono)<div><dt class="text-sm text-tinta-suave">Celular</dt><dd class="dato">{{ $cita->paciente->telefono }}</dd></div>@endif
                    <div><dt class="text-sm text-tinta-suave">Correo</dt><dd class="break-all">{{ $cita->paciente->email }}</dd></div>
                    @if ($cita->paciente->ci)<div><dt class="text-sm text-tinta-suave">CI</dt><dd class="num">{{ $cita->paciente->ci }}</dd></div>@endif
                </dl>
                <div class="mt-4">@include('gestion._resumen-paciente', ['resumen' => $resumenPaciente])</div>
                <a href="{{ route('gestion.pacientes.show', $cita->paciente) }}" class="btn-secundario btn-sm mt-4 w-full">Ver historial completo</a>
            </div>

            @if ($cita->esActiva())
                <div class="tarjeta p-5" x-data="{ abierto: {{ $errors->has('motivo') ? 'true' : 'false' }} }">
                    <h2 class="font-semibold">Cancelar la cita</h2>
                    <p class="mt-1 text-sm text-tinta-suave">El paciente verá el motivo en su historial.</p>
                    <button type="button" class="btn-peligro btn-sm mt-3 w-full" x-show="!abierto" @click="abierto = true">Cancelar cita</button>
                    <form x-show="abierto" x-cloak method="POST" action="{{ route('gestion.citas.cancelar', $cita) }}" class="mt-3 space-y-3">
                        @csrf
                        <textarea name="motivo" rows="2" class="campo" required placeholder="ej. El profesional no podrá atender ese día">{{ old('motivo') }}</textarea>
                        <x-input-error :messages="$errors->get('motivo')" />
                        <div class="flex gap-2">
                            <button class="btn-peligro btn-sm flex-1">Confirmar cancelación</button>
                            <button type="button" class="btn-texto btn-sm" @click="abierto = false">Volver</button>
                        </div>
                    </form>
                </div>
            @endif
        </aside>
    </div>
</x-app-layout>
