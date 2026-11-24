<x-app-layout titulo="Resumen">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">{{ $establecimiento?->nombre ?? 'Resumen general' }}</h1>
                <p class="mt-1 text-[15px] text-tinta-suave">Últimos 30 días, hasta hoy {{ now()->translatedFormat('j \d\e F') }}.</p>
            </div>
            <a href="{{ route('gestion.agenda') }}" class="btn-primario">Abrir agenda</a>
        </div>
    </x-slot>

    {{-- Lo que requiere acción hoy --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('gestion.agenda') }}" class="tarjeta p-5 transition-colors hover:border-petroleo">
            <p class="text-sm text-tinta-suave">Citas hoy</p>
            <p class="num mt-1 text-3xl font-bold">{{ $hoy }}</p>
        </a>
        <a href="{{ route('gestion.citas', ['estado' => 'solicitada']) }}" class="tarjeta p-5 transition-colors hover:border-petroleo">
            <p class="text-sm text-tinta-suave">Por confirmar</p>
            <p class="num mt-1 text-3xl font-bold {{ $porConfirmar ? 'text-estado-solicitada' : '' }}">{{ $porConfirmar }}</p>
        </a>
        <div class="tarjeta p-5">
            <p class="text-sm text-tinta-suave">Tasa de inasistencia</p>
            <p class="num mt-1 text-3xl font-bold">{{ is_null($tasaInasistencia) ? '—' : number_format($tasaInasistencia, 1, ',', '').' %' }}</p>
            <p class="num mt-1 text-xs text-tinta-suave">{{ $porEstado['no_asistio'] ?? 0 }} faltas de {{ ($porEstado['no_asistio'] ?? 0) + ($porEstado['atendida'] ?? 0) }} citas ocurridas</p>
        </div>
        <div class="tarjeta p-5">
            <p class="text-sm text-tinta-suave">Ingresos por sesiones atendidas</p>
            <p class="dato mt-1 text-3xl font-bold">Bs {{ number_format($ingresos, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-tinta-suave">Según el precio publicado</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        {{-- Citas por estado --}}
        <section class="tarjeta p-5">
            <h2 class="font-semibold">Citas por estado</h2>
            <p class="text-sm text-tinta-suave">Citas con fecha en los últimos 30 días</p>
            @php $total = max(1, $porEstado->sum()); @endphp
            <ul class="mt-4 space-y-3">
                @foreach (\App\Models\Cita::ETIQUETAS as $k => $v)
                    @php $n = (int) ($porEstado[$k] ?? 0); @endphp
                    <li class="grid grid-cols-[130px_1fr_40px] items-center gap-3 text-sm">
                        <span>{{ $v }}</span>
                        <span class="h-2.5 overflow-hidden rounded-full bg-lienzo" aria-hidden="true">
                            <span class="block h-full rounded-full {{ ['atendida' => 'bg-estado-atendida', 'no_asistio' => 'bg-estado-falta', 'cancelada' => 'bg-estado-cancelada', 'confirmada' => 'bg-petroleo', 'solicitada' => 'bg-estado-solicitada', 'reprogramada' => 'bg-estado-reprogramada'][$k] }}" style="width: {{ round($n * 100 / $total) }}%"></span>
                        </span>
                        <span class="num text-right font-semibold">{{ $n }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="tarjeta p-5">
            <h2 class="font-semibold">Catálogo publicado</h2>
            <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                @if (! $establecimiento)
                    <div class="rounded-ctl bg-lienzo py-3"><dt class="text-xs text-tinta-suave">Establecimientos</dt><dd class="num text-2xl font-bold">{{ $conteos['establecimientos'] }}</dd></div>
                @endif
                <div class="rounded-ctl bg-lienzo py-3"><dt class="text-xs text-tinta-suave">Profesionales</dt><dd class="num text-2xl font-bold">{{ $conteos['profesionales'] }}</dd></div>
                <div class="rounded-ctl bg-lienzo py-3"><dt class="text-xs text-tinta-suave">Servicios</dt><dd class="num text-2xl font-bold">{{ $conteos['servicios'] }}</dd></div>
            </dl>
            <p class="mt-4 text-sm text-tinta-suave">Valoración promedio</p>
            <x-estrellas class="mt-1" :valor="$promedio" />
        </section>
    </div>

    {{-- Por profesional --}}
    <section class="tarjeta mt-6 overflow-x-auto">
        <h2 class="border-b border-linea px-5 py-4 font-semibold">Asistencia por profesional</h2>
        <table class="w-full min-w-[640px] text-left text-[15px]">
            <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                <tr>
                    <th class="px-5 py-3 font-semibold">Profesional</th>
                    <th class="px-5 py-3 text-right font-semibold">Atendidas</th>
                    <th class="px-5 py-3 text-right font-semibold">Inasistencias</th>
                    <th class="px-5 py-3 text-right font-semibold">Próximas</th>
                    <th class="px-5 py-3 font-semibold">Valoración</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($porProfesional as $p)
                    <tr class="border-b border-linea last:border-0">
                        <td class="px-5 py-3"><a class="font-medium hover:text-petroleo" href="{{ route('gestion.agenda', ['profesional' => $p->id]) }}">{{ $p->user->nombre_completo }}</a></td>
                        <td class="num px-5 py-3 text-right">{{ $p->atendidas }}</td>
                        <td class="num px-5 py-3 text-right {{ $p->faltas ? 'font-semibold text-estado-falta' : '' }}">{{ $p->faltas }}</td>
                        <td class="num px-5 py-3 text-right">{{ $p->proximas }}</td>
                        <td class="px-5 py-3"><x-estrellas tam="h-3.5 w-3.5" :valor="$p->valoraciones_avg_puntuacion" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    @if (auth()->user()->esAdminGeneral())
        <section class="tarjeta mt-6">
            <div class="flex items-center justify-between border-b border-linea px-5 py-4">
                <h2 class="font-semibold">Establecimientos que piden los pacientes</h2>
                <span class="num rounded-full bg-lienzo px-2.5 py-0.5 text-sm font-semibold">{{ $sugerencias->count() }}</span>
            </div>
            <ul class="divide-y divide-linea">
                @forelse ($sugerencias as $s)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $s->establecimiento }}@if ($s->zona)<span class="font-normal text-tinta-suave">, {{ $s->zona }}</span>@endif</p>
                            <p class="text-sm text-tinta-suave">{{ $s->contacto ?: 'Sin contacto' }}, pedido el {{ $s->created_at->translatedFormat('j \d\e F') }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.sugerencias.revisar', $s) }}">
                            @csrf @method('patch')
                            <button class="btn-secundario btn-sm">Marcar revisado</button>
                        </form>
                    </li>
                @empty
                    <li class="px-5 py-5 text-sm text-tinta-suave">No hay pedidos pendientes.</li>
                @endforelse
            </ul>
        </section>
    @endif

    <section class="mt-6">
        <h2 class="titulo-m">Últimas opiniones</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @forelse ($ultimasValoraciones as $v)
                <figure class="tarjeta p-4">
                    <x-estrellas tam="h-3.5 w-3.5" :valor="$v->puntuacion" />
                    @if ($v->comentario)<blockquote class="mt-2 text-[15px]">{{ $v->comentario }}</blockquote>@endif
                    <figcaption class="mt-2 text-sm text-tinta-suave">{{ $v->paciente->nombre_completo }} sobre {{ $v->profesional->user->nombre_completo }}</figcaption>
                </figure>
            @empty
                <p class="text-tinta-suave">Todavía no hay opiniones.</p>
            @endforelse
        </div>
    </section>
</x-app-layout>
