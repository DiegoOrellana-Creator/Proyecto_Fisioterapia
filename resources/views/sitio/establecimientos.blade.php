@php
    $base = array_filter(['q' => $filtros['q'], 'zona' => $filtros['zona'], 'orden' => $orden !== 'valoracion' ? $orden : null]);
    $chip = 'inline-flex min-h-[44px] shrink-0 items-center rounded-full border px-4 text-[15px] font-medium transition-colors';
    $hayFiltros = array_filter($filtros);
@endphp
<x-layouts.sitio titulo="Establecimientos de fisioterapia">
    <div class="mx-auto max-w-sitio px-5 pt-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Establecimientos de fisioterapia en Santa Cruz</h1>
                <p class="mt-1 text-tinta-suave">Precios por sesión en bolivianos, tal como los publica cada establecimiento.</p>
            </div>
            <p class="text-sm text-tinta-suave"><span class="num font-semibold text-tinta">{{ $totalCatalogo }}</span> establecimientos en el catálogo</p>
        </div>

        {{-- Búsqueda y zona --}}
        <form method="get" x-data="{ enviando: false }" @submit="enviando = true" class="tarjeta mt-6 grid gap-3 p-3 md:grid-cols-[1.6fr_1fr_auto]" role="search">
            @if ($filtros['categoria'])<input type="hidden" name="categoria" value="{{ $filtros['categoria'] }}">@endif
            @if ($orden !== 'valoracion')<input type="hidden" name="orden" value="{{ $orden }}">@endif
            <label class="relative block">
                <span class="sr-only">Buscar</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-tinta-suave" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input type="search" name="q" value="{{ $filtros['q'] }}" class="campo pl-10" placeholder="Lesión, tratamiento o profesional (ej. rodilla)">
            </label>
            <label class="block">
                <span class="sr-only">Zona</span>
                <select name="zona" class="campo">
                    <option value="">Todas las zonas</option>
                    @foreach ($zonas as $z)<option @selected($filtros['zona'] === $z)>{{ $z }}</option>@endforeach
                </select>
            </label>
            <button class="btn-primario" :disabled="enviando">
                <span x-show="!enviando">Buscar</span>
                <span x-show="enviando" x-cloak>Buscando…</span>
            </button>
        </form>

        {{-- Taxonomía principal: especialidad, en horizontal e incluyendo "Todas" --}}
        <nav class="mt-4 flex gap-2 overflow-x-auto pb-2 md:flex-wrap md:overflow-visible" aria-label="Especialidad">
            <a href="{{ route('establecimientos.index', $base) }}" @if (! $filtros['categoria']) aria-current="true" @endif
               class="{{ $chip }} {{ ! $filtros['categoria'] ? 'border-petroleo bg-petroleo text-white' : 'border-linea bg-white hover:border-petroleo' }}">Todas</a>
            @foreach ($categorias as $c)
                <a href="{{ route('establecimientos.index', $base + ['categoria' => $c]) }}" @if ($filtros['categoria'] === $c) aria-current="true" @endif
                   class="{{ $chip }} {{ $filtros['categoria'] === $c ? 'border-petroleo bg-petroleo text-white' : 'border-linea bg-white hover:border-petroleo' }}">{{ $c }}</a>
            @endforeach
        </nav>

        {{-- Resultado y orden --}}
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-tinta-suave" aria-live="polite">
                @if ($establecimientos->total())
                    Mostrando <span class="num font-semibold text-tinta">{{ $establecimientos->firstItem() }}–{{ $establecimientos->lastItem() }}</span>
                    de <span class="num font-semibold text-tinta">{{ $establecimientos->total() }}</span>
                    {{ $hayFiltros ? 'que coinciden' : 'establecimientos' }}
                @else
                    Ningún resultado
                @endif
                @if ($hayFiltros)
                    <a href="{{ route('establecimientos.index') }}" class="btn-texto btn-sm ml-1">Quitar filtros</a>
                @endif
            </p>
            <form method="get" class="flex items-center gap-2">
                @foreach (array_filter($filtros) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <label for="orden" class="text-sm text-tinta-suave">Ordenar por</label>
                <select id="orden" name="orden" class="campo w-auto min-w-[180px]" onchange="this.form.submit()">
                    <option value="valoracion" @selected($orden === 'valoracion')>Mejor valorados</option>
                    <option value="precio" @selected($orden === 'precio')>Menor precio</option>
                    <option value="nombre" @selected($orden === 'nombre')>Nombre</option>
                </select>
                <noscript><button class="btn-secundario btn-sm">Aplicar</button></noscript>
            </form>
        </div>

        {{-- Resultados --}}
        <div class="mt-4 grid gap-4">
            @forelse ($establecimientos as $e)
                <article class="tarjeta grid gap-5 p-5 sm:p-6 md:grid-cols-[1fr_260px]">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                            <h2 class="text-lg font-semibold"><a href="{{ route('establecimientos.show', $e) }}" class="hover:text-petroleo">{{ $e->nombre }}</a></h2>
                            <x-disponibilidad :cuando="$proximos[$e->id]" />
                        </div>
                        <p class="mt-1 text-sm text-tinta-suave">{{ $e->zona }}. {{ $e->direccion }}</p>
                        <x-estrellas class="mt-2" :valor="$e->valoraciones_avg_puntuacion" :total="$e->valoraciones_count" />
                        <p class="mt-3 max-w-[70ch] text-[15px] leading-relaxed text-tinta-suave">{{ $e->descripcion }}</p>
                        <ul class="mt-4 flex flex-wrap gap-2" aria-label="Servicios">
                            @foreach ($e->servicios->take(4) as $s)
                                <li @class(['rounded-ctl border px-2.5 py-1 text-sm', 'border-petroleo bg-petroleo-claro' => $filtros['categoria'] === $s->categoria, 'border-linea' => $filtros['categoria'] !== $s->categoria])>{{ $s->nombre }}</li>
                            @endforeach
                            @if ($e->servicios->count() > 4)
                                <li class="px-1 py-1 text-sm text-tinta-suave">y {{ $e->servicios->count() - 4 }} más</li>
                            @endif
                        </ul>
                    </div>
                    <div class="flex flex-col justify-between gap-4 border-t border-linea pt-4 md:border-l md:border-t-0 md:pl-6 md:pt-0">
                        <dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-1">
                            <div>
                                <dt class="text-tinta-suave">Sesión desde</dt>
                                <dd class="dato text-xl font-semibold text-petroleo">Bs {{ number_format($e->servicios_min_precio, 2, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt class="text-tinta-suave">Profesionales</dt>
                                <dd class="num font-semibold">{{ $e->profesionales_count }}</dd>
                            </div>
                            @if ($e->horario_texto)
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-tinta-suave">Atención</dt>
                                    <dd>{{ $e->horario_texto }}</dd>
                                </div>
                            @endif
                        </dl>
                        <a href="{{ route('establecimientos.show', $e) }}" class="btn-primario">Ver horarios y reservar</a>
                    </div>
                </article>
            @empty
                <div class="tarjeta px-6 py-14 text-center">
                    <p class="text-lg font-semibold">Ningún establecimiento coincide con esa búsqueda.</p>
                    <p class="mt-1 text-tinta-suave">Prueba con otra zona o con un término más general, como «espalda» o «rodilla». Si buscas un lugar concreto, pídelo abajo.</p>
                    <a href="{{ route('establecimientos.index') }}" class="btn-secundario mt-5">Ver los {{ $totalCatalogo }} establecimientos</a>
                </div>
            @endforelse
        </div>

        @if ($establecimientos->hasMorePages())
            <div class="mt-6 flex justify-center">
                <a href="{{ $establecimientos->nextPageUrl() }}" class="btn-secundario">Ver más establecimientos</a>
            </div>
        @endif
        @if ($establecimientos->lastPage() > 1)
            <div class="mt-4">{{ $establecimientos->links() }}</div>
        @endif

        {{-- Pedir un establecimiento que no está --}}
        <section id="sugerir" class="mt-12 grid gap-6 rounded-tarjeta border border-dashed border-[#7E9194] bg-white p-6 md:grid-cols-[1fr_1.3fr] md:p-8" aria-labelledby="t-sugerir">
            <div>
                <h2 id="t-sugerir" class="titulo-m">¿No encuentras tu centro de fisioterapia?</h2>
                <p class="mt-2 text-[15px] leading-relaxed text-tinta-suave">Dinos cuál es. Lo contactamos para que publique sus servicios y horarios, y así puedas reservar desde aquí.</p>
            </div>
            @if (session('sugerencia'))
                <div role="status" class="flex items-start gap-3 self-center rounded-ctl border border-green-200 bg-green-50 px-4 py-3 text-[15px]">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-estado-atendida" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"/></svg>
                    {{ session('sugerencia') }}
                </div>
            @else
                <form method="POST" action="{{ route('sugerencias.store') }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div class="sm:col-span-2">
                        <label for="s-establecimiento" class="etiqueta">Nombre del establecimiento o profesional</label>
                        <input id="s-establecimiento" name="establecimiento" value="{{ old('establecimiento') }}" class="campo" required>
                        <x-input-error :messages="$errors->get('establecimiento')" />
                    </div>
                    <div>
                        <label for="s-zona" class="etiqueta">Zona (opcional)</label>
                        <input id="s-zona" name="zona" list="zonas-sugerencia" value="{{ old('zona') }}" class="campo">
                        <datalist id="zonas-sugerencia">@foreach (\App\Models\Establecimiento::ZONAS as $z)<option value="{{ $z }}">@endforeach</datalist>
                    </div>
                    <div>
                        <label for="s-contacto" class="etiqueta">Su teléfono o Instagram (opcional)</label>
                        <input id="s-contacto" name="contacto" value="{{ old('contacto') }}" class="campo">
                    </div>
                    <div class="sm:col-span-2">
                        <button class="btn-secundario">Enviar pedido</button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</x-layouts.sitio>
