<x-layouts.sitio :titulo="$e->nombre" :descripcion="$e->descripcion">
    <div class="mx-auto max-w-sitio px-5 pt-8">
        <nav class="text-sm text-tinta-suave" aria-label="Ruta">
            <a href="{{ route('establecimientos.index') }}" class="hover:text-petroleo">Establecimientos</a>
            <span aria-hidden="true">/</span>
            <span class="text-tinta">{{ $e->nombre }}</span>
        </nav>

        <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_340px]">
            <div class="min-w-0">
                <h1 class="titulo-l">{{ $e->nombre }}</h1>
                <p class="mt-2 text-tinta-suave">{{ $e->direccion }}, {{ $e->zona }}</p>
                <x-estrellas class="mt-3" :valor="$promedio" :total="$totalValoraciones" />
                <p class="mt-5 max-w-[68ch] text-[17px] leading-relaxed">{{ $e->descripcion }}</p>

                {{-- Servicios y precios --}}
                <section class="mt-10" aria-labelledby="t-servicios">
                    <h2 id="t-servicios" class="titulo-m">Servicios y precios</h2>
                    <div class="tarjeta mt-4 overflow-hidden">
                        <table class="w-full text-left text-[15px]">
                            <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Servicio</th>
                                    <th class="hidden px-4 py-3 font-semibold sm:table-cell">Duración</th>
                                    <th class="hidden px-4 py-3 font-semibold sm:table-cell">Sesiones sugeridas</th>
                                    <th class="px-4 py-3 text-right font-semibold">Precio por sesión</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($e->servicios as $s)
                                    <tr class="border-b border-linea last:border-0 align-top">
                                        <td class="px-4 py-4">
                                            <p class="font-semibold">{{ $s->nombre }}</p>
                                            <p class="mt-0.5 text-sm text-tinta-suave">{{ $s->descripcion }}</p>
                                            <p class="num mt-1 text-sm text-tinta-suave sm:hidden">{{ $s->duracion_min }} min, {{ $s->sesiones_sugeridas }} {{ $s->sesiones_sugeridas === 1 ? 'sesión' : 'sesiones' }}</p>
                                        </td>
                                        <td class="num hidden whitespace-nowrap px-4 py-4 sm:table-cell">{{ $s->duracion_min }} min</td>
                                        <td class="num hidden px-4 py-4 sm:table-cell">{{ $s->sesiones_sugeridas }}</td>
                                        <td class="dato whitespace-nowrap px-4 py-4 text-right font-semibold text-petroleo">{{ $s->precio_formateado }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-tinta-suave">Este establecimiento todavía no publicó sus servicios.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-sm text-tinta-suave">Las sesiones sugeridas son orientativas: el número real lo define el profesional en la evaluación.</p>
                </section>

                {{-- Profesionales --}}
                <section class="mt-12" aria-labelledby="t-prof">
                    <h2 id="t-prof" class="titulo-m">Profesionales</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @forelse ($e->profesionales as $p)
                            <article class="tarjeta flex flex-col p-5">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-petroleo-claro font-bold text-petroleo-oscuro" aria-hidden="true">{{ $p->user->iniciales }}</span>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold"><a href="{{ route('profesionales.show', $p) }}" class="hover:text-petroleo">{{ $p->user->nombre_completo }}</a></h3>
                                        <p class="text-sm text-tinta-suave">{{ $p->especialidad }}</p>
                                        <x-estrellas class="mt-1" tam="h-3.5 w-3.5" :valor="$p->valoraciones_avg_puntuacion" />
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-tinta-suave">
                                    Atiende:
                                    {{ $p->horarios->pluck('dia_semana')->unique()->map(fn ($d) => mb_substr(\App\Models\Horario::DIAS[$d], 0, 3))->join(', ') ?: 'sin horario publicado' }}
                                </p>
                                <div class="mt-auto flex gap-2 pt-4">
                                    <a href="{{ route('reservar.create', $p) }}" class="btn-primario btn-sm flex-1">Reservar</a>
                                    <a href="{{ route('profesionales.show', $p) }}" class="btn-secundario btn-sm">Perfil</a>
                                </div>
                            </article>
                        @empty
                            <p class="text-tinta-suave">No hay profesionales con agenda publicada.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Opiniones --}}
                <section class="mt-12" aria-labelledby="t-op">
                    <h2 id="t-op" class="titulo-m">Opiniones de pacientes</h2>
                    <p class="mt-1 text-sm text-tinta-suave">Solo pueden opinar pacientes con una cita atendida.</p>
                    <div class="mt-4 grid gap-3">
                        @forelse ($valoraciones as $v)
                            <figure class="tarjeta p-5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <x-estrellas tam="h-3.5 w-3.5" :valor="$v->puntuacion" />
                                    <span class="text-sm text-tinta-suave">{{ $v->created_at->translatedFormat('j \d\e F Y') }}</span>
                                </div>
                                @if ($v->comentario)<blockquote class="mt-2 text-[15px] leading-relaxed">{{ $v->comentario }}</blockquote>@endif
                                <figcaption class="mt-2 text-sm text-tinta-suave">{{ $v->paciente->nombre }} {{ mb_substr($v->paciente->apellido, 0, 1) }}., atendido por {{ $v->profesional->user->nombre_completo }}</figcaption>
                            </figure>
                        @empty
                            <p class="text-tinta-suave">Todavía no hay opiniones.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Ficha de contacto --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="tarjeta p-5">
                    <h2 class="font-semibold">Contacto y ubicación</h2>
                    <dl class="mt-4 space-y-3 text-[15px]">
                        <div><dt class="text-sm text-tinta-suave">Dirección</dt><dd>{{ $e->direccion }}</dd></div>
                        @if ($e->horario_texto)<div><dt class="text-sm text-tinta-suave">Atención</dt><dd>{{ $e->horario_texto }}</dd></div>@endif
                        @if ($e->telefono)<div><dt class="text-sm text-tinta-suave">Teléfono</dt><dd class="dato">{{ $e->telefono }}</dd></div>@endif
                        @if ($e->email)<div><dt class="text-sm text-tinta-suave">Correo</dt><dd>{{ $e->email }}</dd></div>@endif
                    </dl>
                    <div class="mt-5 grid gap-2">
                        @if ($e->profesionales->isNotEmpty())
                            <a href="#t-prof" class="btn-primario">Elegir profesional y horario</a>
                        @endif
                        @if ($e->whatsapp_url)
                            <a href="{{ $e->whatsapp_url }}" target="_blank" rel="noopener" class="btn-secundario">Escribir por WhatsApp</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.sitio>
