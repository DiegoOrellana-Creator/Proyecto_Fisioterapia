@php
    $u = auth()->user();
    $anterior = $inicioSemana->copy()->subWeek()->toDateString();
    $siguiente = $inicioSemana->copy()->addWeek()->toDateString();
    $params = fn ($semana) => array_filter(['semana' => $semana, 'profesional' => $u->esAdmin() ? $profesionalId : null]);
@endphp
<x-app-layout titulo="Agenda">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Agenda</h1>
                <p class="num mt-1 text-[15px] text-tinta-suave">
                    Semana del {{ $inicioSemana->translatedFormat('j \d\e F') }} al {{ $inicioSemana->copy()->endOfWeek()->translatedFormat('j \d\e F Y') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($u->esAdmin())
                    <form method="get" class="flex items-center gap-2">
                        <input type="hidden" name="semana" value="{{ $inicioSemana->toDateString() }}">
                        <label for="profesional" class="sr-only">Profesional</label>
                        <select id="profesional" name="profesional" class="campo w-auto min-w-[200px]" onchange="this.form.submit()">
                            <option value="">Todos los profesionales</option>
                            @foreach ($profesionales as $p)
                                <option value="{{ $p->id }}" @selected($profesionalId === $p->id)>{{ $p->user->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <a href="{{ route('gestion.agenda', $params($anterior)) }}" class="btn-secundario btn-sm" aria-label="Semana anterior">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
                </a>
                <a href="{{ route('gestion.agenda', $params(now()->toDateString())) }}" class="btn-secundario btn-sm">Esta semana</a>
                <a href="{{ route('gestion.agenda', $params($siguiente)) }}" class="btn-secundario btn-sm" aria-label="Semana siguiente">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Pendientes: lo que requiere acción --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="tarjeta">
            <div class="flex items-center justify-between border-b border-linea px-5 py-4">
                <h2 class="font-semibold">Por confirmar</h2>
                <span class="num rounded-full bg-amber-50 px-2.5 py-0.5 text-sm font-semibold">{{ $porConfirmar->count() }}</span>
            </div>
            <ul class="divide-y divide-linea">
                @forelse ($porConfirmar->take(6) as $c)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="dato text-sm font-semibold">{{ $c->fecha->translatedFormat('D j M') }}, {{ substr($c->hora_inicio, 0, 5) }}
                                @if ($c->estado === 'reprogramada')<span class="font-normal text-estado-reprogramada">(nuevo horario)</span>@endif
                            </p>
                            <p class="truncate text-sm text-tinta-suave">{{ $c->paciente->nombre_completo }}, {{ $c->servicio->nombre }}@if ($u->esAdmin()), con {{ $c->profesional->user->nombre }}@endif</p>
                        </div>
                        @include('gestion._acciones', ['c' => $c])
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-tinta-suave">No hay reservas esperando confirmación.</li>
                @endforelse
            </ul>
            @if ($porConfirmar->count() > 6)
                <a href="{{ route('gestion.citas', ['estado' => 'solicitada']) }}" class="block border-t border-linea px-5 py-3 text-sm font-semibold text-petroleo hover:bg-lienzo">Ver las {{ $porConfirmar->count() }}</a>
            @endif
        </section>

        <section class="tarjeta">
            <div class="flex items-center justify-between border-b border-linea px-5 py-4">
                <h2 class="font-semibold">Falta registrar asistencia</h2>
                <span class="num rounded-full bg-orange-50 px-2.5 py-0.5 text-sm font-semibold">{{ $sinRegistrar->count() }}</span>
            </div>
            <ul class="divide-y divide-linea">
                @forelse ($sinRegistrar->take(6) as $c)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="dato text-sm font-semibold">{{ $c->fecha->translatedFormat('D j M') }}, {{ substr($c->hora_inicio, 0, 5) }}</p>
                            <p class="truncate text-sm text-tinta-suave">{{ $c->paciente->nombre_completo }}, {{ $c->servicio->nombre }}</p>
                        </div>
                        @include('gestion._acciones', ['c' => $c])
                    </li>
                @empty
                    <li class="px-5 py-6 text-sm text-tinta-suave">Todas las citas pasadas tienen su asistencia registrada.</li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Semana --}}
    <section class="mt-8" aria-labelledby="t-semana">
        <h2 id="t-semana" class="titulo-m">Citas de la semana</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @for ($i = 0; $i < 7; $i++)
                @php
                    $dia = $inicioSemana->copy()->addDays($i);
                    $delDia = $citas->get($dia->toDateString(), collect());
                @endphp
                @if ($i < 6 || $delDia->isNotEmpty())
                    <div @class(['tarjeta flex flex-col', 'ring-2 ring-petroleo' => $dia->isToday()])>
                        <div class="flex items-baseline justify-between border-b border-linea px-4 py-3">
                            <h3 class="font-semibold first-letter:uppercase">{{ $dia->translatedFormat('l j') }} @if ($dia->isToday())<span class="text-sm font-normal text-petroleo">hoy</span>@endif</h3>
                            <span class="num text-sm text-tinta-suave">{{ $delDia->count() }} {{ $delDia->count() === 1 ? 'cita' : 'citas' }}</span>
                        </div>
                        <ul class="flex-1 divide-y divide-linea">
                            @forelse ($delDia as $c)
                                <li>
                                    <a href="{{ route('gestion.citas.show', $c) }}" class="grid grid-cols-[52px_1fr] gap-3 px-4 py-3 hover:bg-lienzo">
                                        <span class="dato pt-0.5 text-sm font-bold text-petroleo">{{ substr($c->hora_inicio, 0, 5) }}</span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-[15px] font-medium">{{ $c->paciente->nombre_completo }}</span>
                                            <span class="block truncate text-sm text-tinta-suave">{{ $c->servicio->nombre }}@if ($u->esAdmin() && ! $profesionalId), {{ $c->profesional->user->nombre }}@endif</span>
                                            <x-estado :estado="$c->estado" class="mt-1.5 !h-6 !text-xs" />
                                        </span>
                                    </a>
                                </li>
                            @empty
                                <li class="px-4 py-5 text-sm text-tinta-suave">Sin citas.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            @endfor
        </div>
    </section>
</x-app-layout>
