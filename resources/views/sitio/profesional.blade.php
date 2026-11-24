<x-layouts.sitio :titulo="$p->user->nombre_completo">
    <div class="mx-auto max-w-sitio px-5 pt-8">
        <nav class="text-sm text-tinta-suave" aria-label="Ruta">
            <a href="{{ route('establecimientos.index') }}" class="hover:text-petroleo">Establecimientos</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('establecimientos.show', $p->establecimiento) }}" class="hover:text-petroleo">{{ $p->establecimiento->nombre }}</a>
            <span aria-hidden="true">/</span>
            <span class="text-tinta">{{ $p->user->nombre_completo }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_340px]">
            <div class="min-w-0">
                <div class="flex items-start gap-4">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-petroleo text-xl font-bold text-white" aria-hidden="true">{{ $p->user->iniciales }}</span>
                    <div>
                        <h1 class="titulo-l">{{ $p->user->nombre_completo }}</h1>
                        <p class="mt-1 text-tinta-suave">{{ $p->especialidad }}</p>
                        <x-estrellas class="mt-2" :valor="$promedio" :total="$totalValoraciones" />
                    </div>
                </div>

                <dl class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-[15px]">
                    <div><dt class="text-sm text-tinta-suave">Experiencia</dt><dd class="num font-semibold">{{ $p->anios_experiencia }} años</dd></div>
                    @if ($p->matricula)<div><dt class="text-sm text-tinta-suave">Matrícula profesional</dt><dd class="dato font-semibold">{{ $p->matricula }}</dd></div>@endif
                    <div><dt class="text-sm text-tinta-suave">Atiende en</dt><dd class="font-semibold"><a class="enlace" href="{{ route('establecimientos.show', $p->establecimiento) }}">{{ $p->establecimiento->nombre }}</a></dd></div>
                </dl>

                @if ($p->biografia)
                    <p class="mt-6 max-w-[68ch] text-[17px] leading-relaxed">{{ $p->biografia }}</p>
                @endif

                <section class="mt-10">
                    <h2 class="titulo-m">Servicios que atiende</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($p->servicios as $s)
                            <li class="tarjeta flex flex-col p-4">
                                <p class="font-semibold">{{ $s->nombre }}</p>
                                <p class="num mt-1 text-sm text-tinta-suave">{{ $s->duracion_min }} min por sesión</p>
                                <div class="mt-auto flex items-center justify-between pt-3">
                                    <span class="dato font-semibold text-petroleo">{{ $s->precio_formateado }}</span>
                                    <a href="{{ route('reservar.create', [$p, 'servicio' => $s->id]) }}" class="btn-secundario btn-sm">Reservar</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="mt-12">
                    <h2 class="titulo-m">Opiniones</h2>
                    <div class="mt-4 grid gap-3">
                        @forelse ($valoraciones as $v)
                            <figure class="tarjeta p-5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <x-estrellas tam="h-3.5 w-3.5" :valor="$v->puntuacion" />
                                    <span class="text-sm text-tinta-suave">{{ $v->created_at->translatedFormat('j \d\e F Y') }}</span>
                                </div>
                                @if ($v->comentario)<blockquote class="mt-2 text-[15px] leading-relaxed">{{ $v->comentario }}</blockquote>@endif
                                <figcaption class="mt-2 text-sm text-tinta-suave">{{ $v->paciente->nombre }} {{ mb_substr($v->paciente->apellido, 0, 1) }}.</figcaption>
                            </figure>
                        @empty
                            <p class="text-tinta-suave">Todavía no tiene opiniones.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="tarjeta p-5">
                    <h2 class="font-semibold">Horario de atención</h2>
                    <ul class="mt-3 divide-y divide-linea text-[15px]">
                        @forelse ($p->horarios->groupBy('dia_semana') as $dia => $rangos)
                            <li class="flex justify-between gap-4 py-2.5">
                                <span>{{ \App\Models\Horario::DIAS[$dia] }}</span>
                                <span class="dato text-right text-tinta-suave">{{ $rangos->map->rango->join(', ') }}</span>
                            </li>
                        @empty
                            <li class="py-2.5 text-tinta-suave">Sin horario publicado.</li>
                        @endforelse
                    </ul>
                    @if ($p->horarios->isNotEmpty() && $p->servicios->isNotEmpty())
                        <a href="{{ route('reservar.create', $p) }}" class="btn-primario mt-5 w-full">Ver horarios libres</a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layouts.sitio>
