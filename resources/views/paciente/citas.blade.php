<x-app-layout titulo="Mis citas">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Mis citas</h1>
                <p class="mt-1 text-[15px] text-tinta-suave">Tus próximas sesiones y el historial de las anteriores.</p>
            </div>
            <a href="{{ route('establecimientos.index') }}" class="btn-primario">Reservar otra sesión</a>
        </div>
    </x-slot>

    {{-- Sesiones atendidas que todavía no tienen calificación --}}
    @if ($porValorar->isNotEmpty())
        <section class="mb-8 rounded-tarjeta border border-[#D4B07A] bg-[#FBF5EA] p-5 sm:p-6" aria-labelledby="t-valorar">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-6 w-6 shrink-0 text-[#C98A12]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.8l2.5 5.2 5.7.8-4.1 4 1 5.6L10 14.7l-5.1 2.7 1-5.6-4.1-4 5.7-.8z"/></svg>
                <div>
                    <h2 id="t-valorar" class="titulo-m">¿Cómo te fue en tus sesiones?</h2>
                    <p class="mt-1 text-[15px] text-tinta-suave">
                        Tienes <span class="num font-semibold text-tinta">{{ $porValorar->count() }}</span>
                        {{ $porValorar->count() === 1 ? 'sesión atendida sin calificar' : 'sesiones atendidas sin calificar' }}.
                        Tu opinión ayuda a otros pacientes a elegir.
                    </p>
                </div>
            </div>
            <ul class="mt-4 grid gap-2">
                @foreach ($porValorar->take(4) as $c)
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-ctl bg-white px-4 py-3">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ $c->servicio->nombre }}</p>
                            <p class="text-sm text-tinta-suave">{{ $c->profesional->user->nombre_completo }}, <span class="dato">{{ $c->fecha->format('d/m/Y') }}</span></p>
                        </div>
                        <a href="{{ route('paciente.citas.show', $c) }}#valorar" class="btn-primario btn-sm">Calificar y comentar</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Resumen de asistencia --}}
    @php
        $atendidas = $resumen['atendida'] ?? 0;
        $faltas = $resumen['no_asistio'] ?? 0;
    @endphp
    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ([
            ['Próximas', $proximas->count()],
            ['Atendidas', $atendidas],
            ['No asistí', $faltas],
            ['Canceladas', $resumen['cancelada'] ?? 0],
        ] as [$t, $v])
            <div class="tarjeta px-4 py-3">
                <dt class="text-sm text-tinta-suave">{{ $t }}</dt>
                <dd class="num text-2xl font-bold">{{ $v }}</dd>
            </div>
        @endforeach
    </dl>

    <section class="mt-8" aria-labelledby="t-prox">
        <h2 id="t-prox" class="titulo-m">Próximas</h2>
        <div class="mt-4 grid gap-3">
            @forelse ($proximas as $c)
                <a href="{{ route('paciente.citas.show', $c) }}" class="tarjeta grid gap-4 p-4 transition-colors hover:border-petroleo sm:grid-cols-[96px_1fr_auto] sm:items-center sm:p-5">
                    <div class="flex items-baseline gap-2 sm:block">
                        <p class="text-sm font-semibold text-tinta-suave first-letter:uppercase">{{ $c->fecha->translatedFormat('D j M') }}</p>
                        <p class="dato text-2xl font-bold text-petroleo">{{ substr($c->hora_inicio, 0, 5) }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $c->servicio->nombre }}
                            @if ($c->total_sesiones > 1)<span class="num font-normal text-tinta-suave">, sesión {{ $c->numero_sesion }} de {{ $c->total_sesiones }}</span>@endif
                        </p>
                        <p class="text-sm text-tinta-suave">{{ $c->profesional->user->nombre_completo }}, {{ $c->establecimiento->nombre }}</p>
                    </div>
                    <x-estado :estado="$c->estado" class="w-fit" />
                </a>
            @empty
                <div class="tarjeta px-6 py-10 text-center">
                    <p class="font-semibold">No tienes citas próximas.</p>
                    <p class="mt-1 text-sm text-tinta-suave">Busca un establecimiento y elige un horario libre.</p>
                    <a href="{{ route('establecimientos.index') }}" class="btn-primario mt-4">Buscar establecimiento</a>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-10" aria-labelledby="t-hist">
        <h2 id="t-hist" class="titulo-m">Historial</h2>
        @if ($historial->isEmpty())
            <p class="mt-3 text-tinta-suave">Aquí aparecerán tus citas pasadas, canceladas y su estado de asistencia.</p>
        @else
            <div class="tarjeta mt-4 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-[15px]">
                    <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Fecha</th>
                            <th class="px-4 py-3 font-semibold">Servicio y profesional</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($historial as $c)
                            <tr class="border-b border-linea last:border-0">
                                <td class="dato whitespace-nowrap px-4 py-3">{{ $c->fecha->format('d/m/Y') }} <span class="text-tinta-suave">{{ substr($c->hora_inicio, 0, 5) }}</span></td>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $c->servicio->nombre }}</p>
                                    <p class="text-sm text-tinta-suave">{{ $c->profesional->user->nombre_completo }}</p>
                                </td>
                                <td class="px-4 py-3"><x-estado :estado="$c->estado" /></td>
                                <td class="px-4 py-3 text-right">
                                    @if ($c->puedeValorarse())
                                        <a href="{{ route('paciente.citas.show', $c) }}#valorar" class="btn-secundario btn-sm">Valorar</a>
                                    @else
                                        <a href="{{ route('paciente.citas.show', $c) }}" class="btn-texto btn-sm">Ver</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $historial->links() }}</div>
        @endif
    </section>
</x-app-layout>
