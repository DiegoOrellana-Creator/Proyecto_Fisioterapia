@props(['titulo' => null, 'descripcion' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :titulo="$titulo" :descripcion="$descripcion ?? 'Encuentra establecimientos de fisioterapia en Santa Cruz de la Sierra, compara servicios y precios, y reserva tu sesión en línea.'" />
</head>
<body class="min-h-screen flex flex-col">
    <x-barra-carga />
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 btn-primario">Saltar al contenido</a>

    <header x-data="{ abierto: false }" class="sticky top-0 z-40 border-b border-linea bg-lienzo/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-sitio items-center justify-between gap-6 px-5">
            <x-marca />

            <nav class="hidden items-center gap-1 md:flex" aria-label="Principal">
                @php
                    $links = [
                        ['establecimientos.index', 'Establecimientos', 'establecimientos.*'],
                        ['servicios.index', 'Servicios y precios', 'servicios.*'],
                        ['como-funciona', 'Cómo reservar', 'como-funciona'],
                    ];
                @endphp
                @foreach ($links as [$ruta, $texto, $patron])
                    <a href="{{ route($ruta) }}" @class([
                        'rounded-ctl px-3 py-2 text-[15px] font-medium transition-colors',
                        'text-petroleo bg-petroleo-claro' => request()->routeIs($patron),
                        'text-tinta hover:bg-white' => ! request()->routeIs($patron),
                    ])>{{ $texto }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    <a href="{{ auth()->user()->rutaInicio() }}" class="btn-secundario btn-sm">
                        {{ auth()->user()->esPaciente() ? 'Mis citas' : 'Mi panel' }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-texto btn-sm">Ingresar</a>
                    <a href="{{ route('register') }}" class="btn-primario btn-sm">Crear cuenta</a>
                @endauth
            </div>

            <button type="button" class="btn-texto -mr-2 md:hidden" @click="abierto = !abierto" :aria-expanded="abierto" aria-controls="menu-movil">
                <span class="sr-only">Abrir menú</span>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path x-show="!abierto" d="M4 7h16M4 12h16M4 17h16"/>
                    <path x-show="abierto" x-cloak d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <div id="menu-movil" x-show="abierto" x-cloak class="border-t border-linea bg-white px-5 pb-5 pt-3 md:hidden">
            <nav class="grid gap-1" aria-label="Principal móvil">
                <a href="{{ route('establecimientos.index') }}" class="rounded-ctl px-3 py-3 font-medium hover:bg-lienzo">Establecimientos</a>
                <a href="{{ route('servicios.index') }}" class="rounded-ctl px-3 py-3 font-medium hover:bg-lienzo">Servicios y precios</a>
                <a href="{{ route('como-funciona') }}" class="rounded-ctl px-3 py-3 font-medium hover:bg-lienzo">Cómo reservar</a>
            </nav>
            <div class="mt-3 grid gap-2">
                @auth
                    <a href="{{ auth()->user()->rutaInicio() }}" class="btn-primario">{{ auth()->user()->esPaciente() ? 'Mis citas' : 'Mi panel' }}</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primario">Crear cuenta</a>
                    <a href="{{ route('login') }}" class="btn-secundario">Ingresar</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="contenido" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-20 border-t border-linea bg-white">
        <div class="mx-auto grid max-w-sitio gap-10 px-5 py-12 md:grid-cols-[1.4fr_1fr_1fr]">
            <div class="max-w-sm">
                <x-marca />
                <p class="mt-4 text-sm leading-relaxed text-tinta-suave">
                    Prototipo de plataforma web para la gestión de horarios y reservas de servicios de fisioterapia en Santa Cruz de la Sierra.
                    Los establecimientos y profesionales que aparecen son ficticios.
                </p>
            </div>
            <div>
                <p class="text-sm font-semibold">Pacientes</p>
                <ul class="mt-3 space-y-2 text-sm text-tinta-suave">
                    <li><a class="hover:text-petroleo" href="{{ route('establecimientos.index') }}">Buscar establecimientos</a></li>
                    <li><a class="hover:text-petroleo" href="{{ route('servicios.index') }}">Comparar servicios y precios</a></li>
                    <li><a class="hover:text-petroleo" href="{{ route('como-funciona') }}">Cómo funciona una reserva</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold">Establecimientos</p>
                <ul class="mt-3 space-y-2 text-sm text-tinta-suave">
                    <li><a class="hover:text-petroleo" href="{{ route('login') }}">Ingresar al panel</a></li>
                    <li><a class="hover:text-petroleo" href="{{ route('como-funciona') }}#establecimientos">Qué incluye el panel</a></li>
                    <li><a class="hover:text-petroleo" href="{{ route('guia') }}">Guía de estilo (tokens de interfaz)</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-linea">
            <p class="mx-auto max-w-sitio px-5 py-5 text-xs text-tinta-suave">
                UAGRM, Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones. Metodología de la Investigación, {{ date('Y') }}.
            </p>
        </div>
    </footer>
</body>
</html>
