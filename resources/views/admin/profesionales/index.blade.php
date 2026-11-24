<x-app-layout titulo="Profesionales">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Profesionales</h1>
                <p class="mt-1 text-[15px] text-tinta-suave">Cada profesional tiene su propia cuenta para ver su agenda y registrar asistencias.</p>
            </div>
            <a href="{{ route('admin.profesionales.create') }}" class="btn-primario">Registrar profesional</a>
        </div>
    </x-slot>

    <div class="grid gap-3">
        @forelse ($profesionales as $p)
            <article class="tarjeta grid gap-4 p-5 md:grid-cols-[1fr_auto] md:items-center">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $p->activo ? 'bg-petroleo text-white' : 'bg-linea text-tinta-suave' }} font-bold" aria-hidden="true">{{ $p->user->iniciales }}</span>
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $p->user->nombre_completo }} @unless ($p->activo)<span class="text-sm font-normal text-tinta-suave">(inactivo)</span>@endunless</p>
                        <p class="text-sm text-tinta-suave">{{ $p->especialidad }}@if (auth()->user()->esAdminGeneral()), {{ $p->establecimiento->nombre }}@endif</p>
                        <p class="mt-1 text-sm">
                            <span class="num">{{ $p->servicios->count() }}</span> servicios asignados.
                            @if (! $p->horarios_count)
                                <span class="font-semibold text-estado-falta">Sin horario: no recibe reservas.</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('gestion.horarios', $p) }}" class="btn-secundario btn-sm">Horarios</a>
                    <a href="{{ route('gestion.agenda', ['profesional' => $p->id]) }}" class="btn-secundario btn-sm">Agenda</a>
                    <a href="{{ route('admin.profesionales.edit', $p) }}" class="btn-texto btn-sm">Editar</a>
                </div>
            </article>
        @empty
            <div class="tarjeta px-6 py-12 text-center">
                <p class="font-semibold">Todavía no hay profesionales registrados.</p>
                <a href="{{ route('admin.profesionales.create') }}" class="btn-primario mt-4">Registrar el primero</a>
            </div>
        @endforelse
    </div>
</x-app-layout>
