@php
    $serviciosJson = $p->servicios->mapWithKeys(fn ($s) => [$s->id => [
        'nombre' => $s->nombre, 'precio' => (float) $s->precio, 'duracion' => $s->duracion_min, 'sugeridas' => $s->sesiones_sugeridas,
    ]]);
    $esPaciente = auth()->check() && auth()->user()->esPaciente();
@endphp
<x-layouts.sitio :titulo="'Reservar con '.$p->user->nombre_completo">
    <div class="mx-auto max-w-sitio px-5 pt-8">
        <nav class="text-sm text-tinta-suave" aria-label="Ruta">
            <a href="{{ route('establecimientos.show', $p->establecimiento) }}" class="hover:text-petroleo">{{ $p->establecimiento->nombre }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('profesionales.show', $p) }}" class="hover:text-petroleo">{{ $p->user->nombre_completo }}</a>
            <span aria-hidden="true">/</span>
            <span class="text-tinta">Reservar</span>
        </nav>

        <h1 class="titulo-l mt-4">Reserva tu sesión</h1>

        @if ($p->servicios->isEmpty() || $diasAtencion->isEmpty())
            <div class="tarjeta mt-6 px-6 py-12 text-center">
                <p class="text-lg font-semibold">Este profesional todavía no tiene agenda publicada.</p>
                <p class="mt-1 text-tinta-suave">Elige otro profesional del establecimiento.</p>
                <a href="{{ route('establecimientos.show', $p->establecimiento) }}#t-prof" class="btn-secundario mt-5">Ver profesionales</a>
            </div>
        @else
            <form method="POST" action="{{ route('paciente.reservar', $p) }}"
                  x-data="Object.assign(selectorHorario({
                        url: @js(route('reservar.disponibilidad', $p)),
                        servicio: @js(old('servicio_id', $servicioInicial?->id)),
                        fecha: @js(old('fecha', $fechaInicial)),
                        hora: @js(old('hora', $horaInicial)),
                        dias: @js($diasAtencion),
                        max: {{ $maxDias }},
                  }), { servicios: @js($serviciosJson), sesiones: {{ (int) old('sesiones', 1) }} })"
                  class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]">
                @csrf

                <div class="min-w-0 space-y-6">
                    <x-avisos />

                    <section class="tarjeta p-5 sm:p-6">
                        <h2 class="titulo-m">1. Servicio</h2>
                        <div class="mt-4 grid gap-2">
                            @foreach ($p->servicios as $s)
                                <label class="flex min-h-[56px] cursor-pointer items-center gap-3 rounded-ctl border px-4 py-3 transition-colors"
                                       :class="servicio == '{{ $s->id }}' ? 'border-petroleo bg-petroleo-claro/50' : 'border-linea hover:border-petroleo'">
                                    <input type="radio" name="servicio_id" value="{{ $s->id }}" x-model="servicio" class="h-5 w-5 border-[#7E9194] text-petroleo focus:ring-petroleo">
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold">{{ $s->nombre }}</span>
                                        <span class="num block text-sm text-tinta-suave">{{ $s->duracion_min }} min, {{ $s->sesiones_sugeridas }} {{ $s->sesiones_sugeridas === 1 ? 'sesión sugerida' : 'sesiones sugeridas' }}</span>
                                    </span>
                                    <span class="dato whitespace-nowrap font-semibold text-petroleo">{{ $s->precio_formateado }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="tarjeta p-5 sm:p-6">
                        <h2 class="titulo-m mb-4">2. Día y hora</h2>
                        <x-selector-horario />
                        <x-input-error :messages="$errors->get('hora')" />
                    </section>

                    <section class="tarjeta p-5 sm:p-6">
                        <h2 class="titulo-m">3. Sesiones y comentario</h2>
                        <div class="mt-4 grid gap-5 sm:grid-cols-[220px_1fr]">
                            <div>
                                <label for="sesiones" class="etiqueta">Sesiones a reservar</label>
                                <div class="flex h-11 items-center rounded-ctl border border-[#7E9194] bg-white">
                                    <button type="button" class="h-full w-11 text-xl font-semibold text-petroleo disabled:text-linea" @click="sesiones = Math.max(1, sesiones - 1)" :disabled="sesiones <= 1" aria-label="Una sesión menos">−</button>
                                    <input id="sesiones" name="sesiones" type="number" min="1" max="12" x-model.number="sesiones" class="num h-full w-full border-0 text-center font-semibold focus:ring-0">
                                    <button type="button" class="h-full w-11 text-xl font-semibold text-petroleo disabled:text-linea" @click="sesiones = Math.min(12, sesiones + 1)" :disabled="sesiones >= 12" aria-label="Una sesión más">+</button>
                                </div>
                                <p class="ayuda" x-show="servicios[servicio]">
                                    Sugeridas: <span class="num" x-text="servicios[servicio]?.sugeridas"></span>.
                                    <button type="button" class="font-semibold text-petroleo underline" x-show="servicios[servicio] && sesiones !== servicios[servicio].sugeridas" @click="sesiones = servicios[servicio].sugeridas">Usar sugeridas</button>
                                </p>
                            </div>
                            <div>
                                <label for="nota" class="etiqueta">Comentario para el profesional (opcional)</label>
                                <textarea id="nota" name="nota" rows="3" class="campo" placeholder="ej. Dolor en la rodilla derecha desde hace dos semanas">{{ old('nota') }}</textarea>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-tinta-suave" x-show="sesiones > 1">
                            Se reservan <span class="num font-semibold text-tinta" x-text="sesiones"></span> sesiones, una por semana, el mismo día y a la misma hora.
                        </p>
                    </section>
                </div>

                {{-- Resumen --}}
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="tarjeta overflow-hidden">
                        <div class="border-b border-linea p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-petroleo text-sm font-bold text-white" aria-hidden="true">{{ $p->user->iniciales }}</span>
                                <div>
                                    <p class="font-semibold">{{ $p->user->nombre_completo }}</p>
                                    <p class="text-sm text-tinta-suave">{{ $p->establecimiento->nombre }}</p>
                                </div>
                            </div>
                        </div>
                        <dl class="space-y-3 p-5 text-[15px]">
                            <div class="flex justify-between gap-4"><dt class="text-tinta-suave">Servicio</dt><dd class="text-right font-medium" x-text="servicios[servicio]?.nombre ?? 'Elige uno'"></dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-tinta-suave">Fecha</dt><dd class="text-right font-medium first-letter:uppercase" x-text="fechaLegible || 'Elige un día'"></dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-tinta-suave">Hora</dt><dd class="dato text-right font-medium" x-text="hora ?? 'Elige un horario'"></dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-tinta-suave">Sesiones</dt><dd class="num text-right font-medium" x-text="sesiones"></dd></div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-linea pt-3">
                                <dt class="font-semibold">Total estimado</dt>
                                <dd class="dato text-xl font-bold text-petroleo" x-text="servicios[servicio] ? 'Bs ' + (servicios[servicio].precio * sesiones).toLocaleString('es-BO', { minimumFractionDigits: 2 }) : '—'"></dd>
                            </div>
                        </dl>
                        <div class="border-t border-linea bg-lienzo p-5">
                            @if ($esPaciente)
                                <button type="submit" class="btn-primario w-full" :disabled="!hora || !servicio">Enviar reserva</button>
                                <p class="mt-3 text-sm text-tinta-suave">Se paga en el establecimiento. Tu cita queda solicitada hasta que la confirmen.</p>
                            @elseif (auth()->check())
                                <p class="text-sm text-tinta-suave">Estás con una cuenta de {{ auth()->user()->rol === 'admin' ? 'administrador' : 'profesional' }}. Para reservar, ingresa con una cuenta de paciente.</p>
                            @else
                                {{-- Camino bloqueado: hace falta sesión --}}
                                <p class="text-sm font-semibold">Para enviar la reserva necesitas una cuenta de paciente.</p>
                                <p class="mt-1 text-sm text-tinta-suave">Al ingresar vuelves aquí con el horario que elegiste.</p>
                                <div class="mt-4 grid gap-2">
                                    <a :href="@js(route('login')) + '?volver=' + encodeURIComponent(location.pathname + '?servicio=' + servicio + '&fecha=' + (fecha ?? '') + '&hora=' + (hora ?? ''))" class="btn-primario">Ingresar y reservar</a>
                                    <a :href="@js(route('register')) + '?volver=' + encodeURIComponent(location.pathname + '?servicio=' + servicio + '&fecha=' + (fecha ?? '') + '&hora=' + (hora ?? ''))" class="btn-secundario">Crear cuenta</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>
            </form>
        @endif
    </div>
</x-layouts.sitio>
