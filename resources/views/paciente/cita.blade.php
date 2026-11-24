<x-app-layout :titulo="'Cita '.$cita->codigo">
    <x-slot name="header">
        <a href="{{ route('paciente.citas') }}" class="text-sm font-medium text-petroleo hover:underline">Mis citas</a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <h1 class="titulo-l">{{ $cita->servicio->nombre }}</h1>
            <x-estado :estado="$cita->estado" />
        </div>
        <p class="dato mt-1 text-sm text-tinta-suave">Código {{ $cita->codigo }}</p>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="min-w-0 space-y-6">
            <section class="tarjeta p-5 sm:p-6">
                <dl class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-tinta-suave">Fecha y hora</dt>
                        <dd class="mt-0.5 text-lg font-semibold first-letter:uppercase">{{ $cita->fecha->translatedFormat('l j \d\e F') }}</dd>
                        <dd class="dato text-lg font-semibold text-petroleo">{{ $cita->rango_hora }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-tinta-suave">Profesional</dt>
                        <dd class="mt-0.5 font-semibold"><a class="hover:text-petroleo" href="{{ route('profesionales.show', $cita->profesional) }}">{{ $cita->profesional->user->nombre_completo }}</a></dd>
                        <dd class="text-sm text-tinta-suave">{{ $cita->profesional->especialidad }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-tinta-suave">Lugar</dt>
                        <dd class="mt-0.5 font-semibold">{{ $cita->establecimiento->nombre }}</dd>
                        <dd class="text-sm text-tinta-suave">{{ $cita->establecimiento->direccion }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-tinta-suave">Precio de la sesión</dt>
                        <dd class="dato mt-0.5 font-semibold">Bs {{ number_format($cita->precio, 2, ',', '.') }}</dd>
                        <dd class="text-sm text-tinta-suave">Se paga en el establecimiento</dd>
                    </div>
                </dl>

                @if ($cita->nota_paciente)
                    <div class="mt-5 rounded-ctl bg-lienzo p-4">
                        <p class="text-sm font-semibold">Tu comentario</p>
                        <p class="mt-1 text-[15px]">{{ $cita->nota_paciente }}</p>
                    </div>
                @endif
                @if ($cita->motivo_cancelacion && $cita->estado === 'cancelada')
                    <div class="mt-5 rounded-ctl border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-semibold">Motivo de la cancelación</p>
                        <p class="mt-1 text-[15px]">{{ $cita->motivo_cancelacion }}</p>
                    </div>
                @endif

                @if ($cita->estado === 'solicitada' || $cita->estado === 'reprogramada')
                    <p class="mt-5 text-sm text-tinta-suave">El establecimiento todavía debe confirmar esta cita. Si necesitas avisar algo, escribe a {{ $cita->establecimiento->whatsapp ? 'su WhatsApp '.$cita->establecimiento->whatsapp : 'su teléfono '.$cita->establecimiento->telefono }}.</p>
                @endif
            </section>

            @if ($serie->count() > 1)
                <section class="tarjeta p-5 sm:p-6">
                    <h2 class="titulo-m">Sesiones del tratamiento</h2>
                    <ol class="mt-4 divide-y divide-linea">
                        @foreach ($serie as $s)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3 {{ $s->id === $cita->id ? 'font-semibold' : '' }}">
                                <a href="{{ route('paciente.citas.show', $s) }}" class="num hover:text-petroleo">
                                    Sesión {{ $s->numero_sesion }}: {{ $s->fecha->translatedFormat('D j M') }}, {{ substr($s->hora_inicio, 0, 5) }}
                                </a>
                                <x-estado :estado="$s->estado" />
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Valoración --}}
            @if ($cita->valoracion)
                <section class="tarjeta p-5 sm:p-6">
                    <h2 class="titulo-m">Tu valoración</h2>
                    <x-estrellas class="mt-3" :valor="$cita->valoracion->puntuacion" />
                    @if ($cita->valoracion->comentario)<p class="mt-2 text-[15px]">{{ $cita->valoracion->comentario }}</p>@endif
                </section>
            @elseif ($cita->puedeValorarse())
                <section id="valorar" class="tarjeta p-5 sm:p-6" x-data="{ p: {{ (int) old('puntuacion', 0) }} }">
                    <h2 class="titulo-m">¿Cómo fue la atención?</h2>
                    <p class="mt-1 text-sm text-tinta-suave">Tu opinión aparece en el perfil del profesional con tu nombre y la inicial de tu apellido.</p>
                    <form method="POST" action="{{ route('paciente.citas.valorar', $cita) }}" class="mt-4 space-y-4">
                        @csrf
                        <fieldset>
                            <legend class="etiqueta">Puntuación</legend>
                            <div class="flex gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="puntuacion" value="{{ $i }}" class="peer sr-only" x-model.number="p">
                                        <svg class="h-10 w-10 rounded-ctl p-1 peer-focus-visible:ring-2 peer-focus-visible:ring-petroleo" :class="p >= {{ $i }} ? 'text-[#C98A12]' : 'text-linea'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.8l2.5 5.2 5.7.8-4.1 4 1 5.6L10 14.7l-5.1 2.7 1-5.6-4.1-4 5.7-.8z"/></svg>
                                        <span class="sr-only">{{ $i }} {{ $i === 1 ? 'estrella' : 'estrellas' }}</span>
                                    </label>
                                @endfor
                            </div>
                            <x-input-error :messages="$errors->get('puntuacion')" />
                        </fieldset>
                        <div>
                            <label for="comentario" class="etiqueta">Comentario (opcional)</label>
                            <textarea id="comentario" name="comentario" rows="3" class="campo">{{ old('comentario') }}</textarea>
                        </div>
                        <button class="btn-primario" :disabled="!p">Publicar valoración</button>
                    </form>
                </section>
            @endif

            <section>
                <h2 class="titulo-m">Historial de la cita</h2>
                <ol class="mt-4 space-y-3 border-l-2 border-linea pl-5">
                    @foreach ($cita->historial as $h)
                        <li class="relative">
                            <span class="absolute -left-[27px] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-petroleo" aria-hidden="true"></span>
                            <p class="text-[15px]"><span class="font-semibold">{{ \App\Models\Cita::ETIQUETAS[$h->estado_nuevo] ?? $h->estado_nuevo }}</span>
                                @if ($h->detalle)<span class="text-tinta-suave">: {{ $h->detalle }}</span>@endif
                            </p>
                            <p class="num text-sm text-tinta-suave">{{ $h->created_at->format('d/m/Y H:i') }}@if ($h->user), {{ $h->user->nombre_completo }}@endif</p>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        @if ($cita->modificablePorPaciente())
            <aside class="space-y-4 lg:sticky lg:top-8 lg:self-start">
                <div class="tarjeta p-5">
                    <h2 class="font-semibold">¿Necesitas otro horario?</h2>
                    <p class="mt-1 text-sm text-tinta-suave">Elige uno libre del mismo profesional. El establecimiento debe confirmarlo otra vez.</p>
                    <a href="{{ route('paciente.citas.reprogramar', $cita) }}" class="btn-secundario mt-4 w-full">Reprogramar</a>
                </div>
                <div class="tarjeta p-5" x-data="{ abierto: {{ $errors->has('motivo') ? 'true' : 'false' }} }">
                    <h2 class="font-semibold">Cancelar la cita</h2>
                    <p class="mt-1 text-sm text-tinta-suave">El horario se libera para otro paciente.</p>
                    <button type="button" class="btn-peligro mt-4 w-full" x-show="!abierto" @click="abierto = true">Cancelar cita</button>
                    <form x-show="abierto" x-cloak method="POST" action="{{ route('paciente.citas.cancelar', $cita) }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label for="motivo" class="etiqueta">Motivo</label>
                            <textarea id="motivo" name="motivo" rows="2" class="campo" required placeholder="ej. Tengo un viaje ese día">{{ old('motivo') }}</textarea>
                            <x-input-error :messages="$errors->get('motivo')" />
                        </div>
                        <div class="flex gap-2">
                            <button class="btn-peligro flex-1">Confirmar cancelación</button>
                            <button type="button" class="btn-texto" @click="abierto = false">Volver</button>
                        </div>
                    </form>
                </div>
            </aside>
        @endif
    </div>
</x-app-layout>
