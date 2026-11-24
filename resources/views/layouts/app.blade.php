<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :titulo="$titulo ?? null" />
</head>
<body class="min-h-screen">
    <x-barra-carga />
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 btn-primario">Saltar al contenido</a>

    <div x-data="{ menu: false }" class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        @include('layouts.navigation')

        <div class="min-w-0">
            {{-- Barra superior móvil --}}
            <div class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-linea bg-lienzo/95 px-4 backdrop-blur lg:hidden">
                <x-marca />
                <button type="button" class="btn-texto -mr-2" @click="menu = true" aria-controls="panel-nav" :aria-expanded="menu">
                    <span class="sr-only">Abrir menú</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>

            @isset($header)
                <header class="border-b border-linea bg-white">
                    <div class="mx-auto max-w-6xl px-5 py-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main id="contenido" class="mx-auto max-w-6xl px-5 py-8 lg:px-8">
                <x-avisos />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
