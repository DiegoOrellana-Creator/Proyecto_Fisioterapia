<x-layouts.sitio titulo="Servicios y precios">
    <div class="mx-auto max-w-sitio px-5 pt-10">
        <h1 class="titulo-l">Servicios y precios</h1>
        <p class="mt-1 max-w-[64ch] text-tinta-suave">Compara el mismo tratamiento en distintos establecimientos. El precio es por sesión.</p>

        <div class="mt-6 flex gap-2 overflow-x-auto pb-2 md:flex-wrap md:overflow-visible" role="tablist" aria-label="Especialidades">
            @php $chip = 'inline-flex min-h-[44px] shrink-0 items-center rounded-full border px-4 text-[15px] font-medium'; @endphp
            <a href="{{ route('servicios.index', ['orden' => $orden]) }}" role="tab" aria-selected="{{ $categoria ? 'false' : 'true' }}"
               class="{{ $chip }} {{ ! $categoria ? 'border-petroleo bg-petroleo text-white' : 'border-linea bg-white hover:border-petroleo' }}">Todas</a>
            @foreach ($categorias as $c)
                <a href="{{ route('servicios.index', ['categoria' => $c, 'orden' => $orden]) }}" role="tab" aria-selected="{{ $categoria === $c ? 'true' : 'false' }}"
                   class="{{ $chip }} {{ $categoria === $c ? 'border-petroleo bg-petroleo text-white' : 'border-linea bg-white hover:border-petroleo' }}">{{ $c }}</a>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-tinta-suave"><span class="num font-semibold text-tinta">{{ $servicios->total() }}</span> servicios</p>
            <form method="get" class="flex items-center gap-2">
                @if ($categoria)<input type="hidden" name="categoria" value="{{ $categoria }}">@endif
                <label for="orden" class="text-sm text-tinta-suave">Ordenar por</label>
                <select id="orden" name="orden" class="campo w-auto min-w-[170px]" onchange="this.form.submit()">
                    <option value="precio" @selected($orden === 'precio')>Menor precio</option>
                    <option value="duracion" @selected($orden === 'duracion')>Menor duración</option>
                    <option value="nombre" @selected($orden === 'nombre')>Nombre</option>
                </select>
                <noscript><button class="btn-secundario btn-sm">Aplicar</button></noscript>
            </form>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($servicios as $s)
                <article class="tarjeta flex flex-col p-5">
                    <p class="text-sm text-tinta-suave">{{ $s->categoria }}</p>
                    <h2 class="mt-1 font-semibold leading-snug">{{ $s->nombre }}</h2>
                    <p class="mt-1 text-sm"><a href="{{ route('establecimientos.show', $s->establecimiento) }}" class="text-petroleo hover:underline">{{ $s->establecimiento->nombre }}</a>, {{ $s->establecimiento->zona }}</p>
                    @if ($s->descripcion)<p class="mt-3 text-sm leading-relaxed text-tinta-suave">{{ $s->descripcion }}</p>@endif
                    <dl class="mt-auto grid grid-cols-3 gap-2 border-t border-linea pt-4 mt-5 text-sm">
                        <div><dt class="text-tinta-suave">Precio</dt><dd class="dato font-bold text-petroleo">{{ $s->precio_formateado }}</dd></div>
                        <div><dt class="text-tinta-suave">Duración</dt><dd class="num font-semibold">{{ $s->duracion_min }} min</dd></div>
                        <div><dt class="text-tinta-suave">Sesiones</dt><dd class="num font-semibold">{{ $s->sesiones_sugeridas }}</dd></div>
                    </dl>
                    <a href="{{ route('establecimientos.show', $s->establecimiento) }}#t-prof" class="btn-secundario btn-sm mt-4">
                        Elegir entre {{ $s->profesionales_count }} {{ $s->profesionales_count === 1 ? 'profesional' : 'profesionales' }}
                    </a>
                </article>
            @empty
                <div class="tarjeta col-span-full px-6 py-14 text-center">
                    <p class="text-lg font-semibold">No hay servicios publicados en esta especialidad.</p>
                    <a href="{{ route('servicios.index') }}" class="btn-secundario mt-5">Ver todas las especialidades</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $servicios->links() }}</div>
    </div>
</x-layouts.sitio>
