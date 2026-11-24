@php
    $u = auth()->user();
    $grupos = [];

    if ($u->esPaciente()) {
        $grupos['Mi cuenta'] = [
            ['paciente.citas', 'Mis citas', 'paciente.*', 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
            ['establecimientos.index', 'Reservar una sesión', 'establecimientos.*', 'M12 5v14M5 12h14'],
        ];
    }

    if ($u->esProfesional()) {
        $grupos['Mi trabajo'] = [
            ['gestion.agenda', 'Agenda', 'gestion.agenda', 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
            ['gestion.citas', 'Citas y asistencias', 'gestion.citas*', 'M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01'],
            ['gestion.horarios.propios', 'Mis horarios', 'gestion.horarios*', 'M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        if ($u->profesional) {
            $grupos['Mi trabajo'][] = ['profesionales.show', 'Ver mi perfil público', '__', 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.5 12C4 7.5 7.7 5 12 5s8 2.5 9.5 7c-1.5 4.5-5.2 7-9.5 7s-8-2.5-9.5-7z', $u->profesional];
        }
    }

    if ($u->esAdmin()) {
        $grupos['Operación'] = [
            ['admin.dashboard', 'Resumen', 'admin.dashboard', 'M4 13h6V4H4zM14 20h6v-9h-6zM4 20h6v-3H4zM14 7h6V4h-6z'],
            ['gestion.agenda', 'Agenda', 'gestion.agenda', 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
            ['gestion.citas', 'Citas y asistencias', 'gestion.citas*', 'M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01'],
        ];
        $grupos['Configuración'] = [
            ['admin.establecimientos.index', $u->esAdminGeneral() ? 'Establecimientos' : 'Mi establecimiento', 'admin.establecimientos.*', 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6'],
            ['admin.profesionales.index', 'Profesionales', 'admin.profesionales.*', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0'],
            ['admin.servicios.index', 'Servicios y precios', 'admin.servicios.*', 'M4 6h16M4 12h10M4 18h7'],
        ];
        if ($u->esAdminGeneral()) {
            $grupos['Configuración'][] = ['admin.usuarios.index', 'Usuarios', 'admin.usuarios.*', 'M17 20v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M10 11a4 4 0 100-8 4 4 0 000 8zM21 20v-1a4 4 0 00-3-3.9M16 3.1a4 4 0 010 7.8'];
        }
    }
@endphp

<div x-show="menu" x-cloak class="fixed inset-0 z-40 bg-tinta/40 lg:hidden" @click="menu = false" aria-hidden="true"></div>

<aside id="panel-nav"
       class="fixed inset-y-0 left-0 z-50 flex w-[272px] -translate-x-full flex-col border-r border-linea bg-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:w-auto lg:translate-x-0"
       :class="menu ? 'translate-x-0' : ''"
       @keydown.escape.window="menu = false">
    <div class="flex h-16 items-center justify-between px-5">
        <x-marca />
        <button type="button" class="btn-texto -mr-2 lg:hidden" @click="menu = false">
            <span class="sr-only">Cerrar menú</span>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    @if ($u->esAdmin())
        <p class="mx-5 mb-2 rounded-ctl bg-lienzo px-3 py-2 text-xs text-tinta-suave">
            {{ $u->esAdminGeneral() ? 'Administración general' : $u->establecimiento?->nombre }}
        </p>
    @endif

    <nav class="flex-1 overflow-y-auto px-3 pb-6" aria-label="Panel">
        @foreach ($grupos as $titulo => $items)
            <p class="px-3 pb-1 pt-5 text-xs font-semibold text-tinta-suave">{{ $titulo }}</p>
            <ul class="space-y-0.5">
                @foreach ($items as $item)
                    @php
                        [$ruta, $texto, $patron, $icono] = $item;
                        $activo = request()->routeIs($patron);
                        $url = isset($item[4]) ? route($ruta, $item[4]) : route($ruta);
                    @endphp
                    <li>
                        <a href="{{ $url }}" @if ($activo) aria-current="page" @endif @class([
                            'flex min-h-[44px] items-center gap-3 rounded-ctl px-3 text-[15px] font-medium transition-colors',
                            'bg-petroleo-claro text-petroleo-oscuro' => $activo,
                            'text-tinta hover:bg-lienzo' => ! $activo,
                        ])>
                            <svg class="h-5 w-5 shrink-0 {{ $activo ? 'text-petroleo' : 'text-tinta-suave' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icono }}"/></svg>
                            {{ $texto }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="border-t border-linea p-3">
        <div class="flex items-center gap-3 px-2 py-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-petroleo text-sm font-bold text-white" aria-hidden="true">{{ $u->iniciales }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">{{ $u->nombre_completo }}</p>
                <p class="truncate text-xs text-tinta-suave">{{ $u->email }}</p>
            </div>
        </div>
        <div class="mt-1 grid grid-cols-2 gap-1">
            <a href="{{ route('profile.edit') }}" class="btn-texto btn-sm justify-start">Mi perfil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-texto btn-sm w-full justify-start">Cerrar sesión</button>
            </form>
        </div>
        <a href="{{ route('inicio') }}" class="btn-texto btn-sm mt-1 w-full justify-start text-tinta-suave">Ir al sitio público</a>
    </div>
</aside>
