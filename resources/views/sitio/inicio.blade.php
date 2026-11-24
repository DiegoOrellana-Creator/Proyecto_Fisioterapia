<x-layouts.sitio>
    {{-- Portada: buscador + tablero de horarios libres reales --}}
    <section class="relative overflow-hidden">
        <div class="mx-auto grid max-w-sitio items-start gap-12 px-5 pb-16 pt-12 lg:grid-cols-[1.05fr_1fr] lg:pt-20">
            <div>
                <h1 class="titulo-xl max-w-[16ch]">Tu sesión de fisioterapia, reservada sin esperar una llamada.</h1>
                <p class="mt-5 max-w-[52ch] text-lg leading-relaxed text-tinta-suave">
                    Compara establecimientos de Santa Cruz de la Sierra, mira tratamientos, precios y horarios libres, y reserva en línea.
                </p>

                <form action="{{ route('establecimientos.index') }}" method="get" class="tarjeta mt-8 grid gap-3 p-3 sm:grid-cols-[1fr_auto]" role="search">
                    <div class="grid gap-3 sm:grid-cols-[1.4fr_1fr]">
                        <label class="relative block">
                            <span class="sr-only">Qué buscas</span>
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-tinta-suave" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            <input type="search" name="q" class="campo pl-10" placeholder="Lesión, tratamiento o nombre (ej. rodilla)">
                        </label>
                        <label class="block">
                            <span class="sr-only">Zona</span>
                            <select name="zona" class="campo">
                                <option value="">Toda la ciudad</option>
                                @foreach ($zonas as $z)<option>{{ $z }}</option>@endforeach
                            </select>
                        </label>
                    </div>
                    <button class="btn-primario">Buscar</button>
                </form>

                <p class="mt-4 text-sm text-tinta-suave">
                    <span class="num font-semibold text-tinta">{{ $totales['establecimientos'] }}</span> establecimientos,
                    <span class="num font-semibold text-tinta">{{ $totales['profesionales'] }}</span> fisioterapeutas y
                    <span class="num font-semibold text-tinta">{{ $totales['servicios'] }}</span> servicios con precio publicado.
                </p>
            </div>

            {{-- Tablero de horarios libres --}}
            <div class="relative lg:mt-2">
                <span class="cinta hidden sm:block -right-10 top-6 z-10 w-48 rotate-[32deg]" aria-hidden="true"></span>
                <span class="cinta hidden sm:block -right-6 top-14 z-10 w-36 -rotate-[14deg] !bg-[#2BA7B5]" aria-hidden="true"></span>

                <div class="tarjeta relative overflow-hidden">
                    <div class="flex items-center justify-between border-b border-linea px-5 py-4">
                        <div>
                            <h2 class="text-base font-semibold">Próximos horarios libres</h2>
                            <p class="text-sm text-tinta-suave">Calculados ahora con la agenda de cada profesional</p>
                        </div>
                        <span class="relative mr-8 flex h-2.5 w-2.5" aria-hidden="true">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-estado-atendida opacity-50"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-estado-atendida"></span>
                        </span>
                    </div>

                    @forelse ($proximos as $slot)
                        @php
                            $dia = $slot['fecha']->isToday() ? 'Hoy' : ($slot['fecha']->isTomorrow() ? 'Mañana' : ucfirst($slot['fecha']->translatedFormat('l j')));
                        @endphp
                        <a href="{{ route('reservar.create', [$slot['profesional'], 'servicio' => $slot['servicio']->id, 'fecha' => $slot['fecha']->toDateString(), 'hora' => $slot['hora']]) }}"
                           class="group grid grid-cols-[68px_1fr] items-center gap-3 border-b sm:grid-cols-[88px_1fr_auto] sm:gap-4 border-linea px-5 py-4 last:border-b-0 hover:bg-lienzo">
                            <div>
                                <p class="text-xs font-semibold text-tinta-suave">{{ $dia }}</p>
                                <p class="dato text-2xl font-bold tracking-[-0.02em] text-petroleo">{{ $slot['hora'] }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold">{{ $slot['profesional']->user->nombre_completo }}</p>
                                <p class="truncate text-sm text-tinta-suave">{{ $slot['servicio']->nombre }}</p>
                                <p class="truncate text-sm text-tinta-suave">{{ $slot['profesional']->establecimiento->nombre }}, {{ $slot['profesional']->establecimiento->zona }}</p>
                            </div>
                            <span class="btn-secundario btn-sm hidden group-hover:bg-petroleo group-hover:text-white sm:inline-flex">Reservar</span>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="font-semibold">No hay horarios libres en los próximos 7 días.</p>
                            <p class="mt-1 text-sm text-tinta-suave">Revisa los establecimientos para ver fechas más adelante.</p>
                            <a href="{{ route('establecimientos.index') }}" class="btn-secundario mt-4">Ver establecimientos</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Tratamientos por especialidad --}}
    <section class="border-y border-linea bg-white">
        <div class="mx-auto max-w-sitio px-5 py-12">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="titulo-l">¿Qué necesitas tratar?</h2>
                    <p class="mt-1 text-tinta-suave">Elige una especialidad para comparar precios, duración y sesiones sugeridas.</p>
                </div>
                <a href="{{ route('servicios.index') }}" class="enlace">Ver todos los servicios</a>
            </div>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($categorias as $c)
                    <a href="{{ route('servicios.index', ['categoria' => $c]) }}" class="inline-flex min-h-[44px] items-center rounded-full border border-linea bg-lienzo px-4 text-[15px] font-medium hover:border-petroleo hover:text-petroleo">{{ $c }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Establecimientos mejor valorados --}}
    <section class="mx-auto max-w-sitio px-5 py-16">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h2 class="titulo-l">Mejor valorados por pacientes</h2>
            <a href="{{ route('establecimientos.index') }}" class="enlace">Ver los {{ $totales['establecimientos'] }} establecimientos</a>
        </div>
        <div class="mt-6 grid gap-5 md:grid-cols-3">
            @foreach ($destacados as $e)
                <a href="{{ route('establecimientos.show', $e) }}" class="tarjeta flex flex-col p-6 transition-colors hover:border-petroleo">
                    <p class="text-sm text-tinta-suave">{{ $e->zona }}</p>
                    <h3 class="mt-1 text-lg font-semibold leading-snug">{{ $e->nombre }}</h3>
                    <x-estrellas class="mt-2" :valor="$e->valoraciones_avg_puntuacion" />
                    <p class="mt-3 line-clamp-3 text-[15px] leading-relaxed text-tinta-suave">{{ $e->descripcion }}</p>
                    <p class="mt-auto pt-5 text-sm text-tinta-suave">
                        <span class="num font-semibold text-tinta">{{ $e->profesionales_count }}</span> profesionales,
                        <span class="num font-semibold text-tinta">{{ $e->servicios_count }}</span> servicios
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Cómo funciona: es una secuencia real, por eso va numerada --}}
    <section class="mx-auto max-w-sitio px-5">
        <div class="rounded-tarjeta bg-petroleo-oscuro px-6 py-12 text-white sm:px-12">
            <h2 class="titulo-l max-w-[24ch]">Reservar te toma menos que esperar a que contesten el WhatsApp.</h2>
            <ol class="mt-10 grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['Elige', 'Busca por zona o tratamiento y compara precio, duración y opiniones.'],
                    ['Reserva', 'Selecciona un horario libre. Si tu tratamiento tiene varias sesiones, resérvalas de una vez.'],
                    ['Recibe la confirmación', 'El establecimiento confirma tu cita. Puedes cancelar o reprogramar desde tu cuenta.'],
                ] as $i => [$t, $d])
                    <li class="border-t border-white/25 pt-5">
                        <span class="num text-sm font-semibold text-white/60">Paso {{ $i + 1 }}</span>
                        <p class="mt-1 text-lg font-semibold">{{ $t }}</p>
                        <p class="mt-2 text-[15px] leading-relaxed text-white/75">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('establecimientos.index') }}" class="btn bg-white text-petroleo-oscuro hover:bg-petroleo-claro">Buscar establecimiento</a>
                <a href="{{ route('como-funciona') }}" class="btn border border-white/40 text-white hover:bg-white/10">Más detalles</a>
            </div>
        </div>
    </section>
</x-layouts.sitio>
