<x-app-layout titulo="Horarios de atención">
    <x-slot name="header">
        @if (auth()->user()->esAdmin())
            <a href="{{ route('admin.profesionales.index') }}" class="text-sm font-medium text-petroleo hover:underline">Profesionales</a>
        @endif
        <h1 class="titulo-l mt-1">Horarios de {{ auth()->user()->profesional?->id === $p->id ? 'atención' : $p->user->nombre_completo }}</h1>
        <p class="mt-1 text-[15px] text-tinta-suave">Los pacientes solo pueden reservar dentro de estos horarios. La duración de cada cita depende del servicio elegido.</p>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            {{-- Semana --}}
            <section class="tarjeta">
                <h2 class="border-b border-linea px-5 py-4 font-semibold">Semana tipo</h2>
                <ul class="divide-y divide-linea">
                    @foreach (\App\Models\Horario::DIAS as $n => $dia)
                        <li class="grid gap-2 px-5 py-3 sm:grid-cols-[120px_1fr] sm:items-center">
                            <span class="font-medium">{{ $dia }}</span>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($porDia->get($n, collect()) as $h)
                                    <form method="POST" action="{{ route('gestion.horarios.destroy', [$p, $h]) }}" class="inline-flex items-center rounded-ctl border border-linea bg-lienzo pl-3"
                                          onsubmit="return confirm('¿Quitar el horario de {{ $dia }} {{ $h->rango }}?')">
                                        @csrf @method('delete')
                                        <span class="dato whitespace-nowrap text-[15px] font-semibold">{{ $h->rango }}</span>
                                        <button class="ml-1 flex h-9 w-9 items-center justify-center rounded-ctl text-tinta-suave hover:text-estado-cancelada" aria-label="Quitar {{ $dia }} {{ $h->rango }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                        </button>
                                    </form>
                                @empty
                                    <span class="text-sm text-tinta-suave">No atiende</span>
                                @endforelse
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Bloqueos --}}
            <section class="tarjeta">
                <h2 class="border-b border-linea px-5 py-4 font-semibold">Ausencias y bloqueos próximos</h2>
                <ul class="divide-y divide-linea">
                    @forelse ($bloqueos as $b)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                            <div>
                                <p class="num font-medium">{{ $b->inicio->format('d/m/Y H:i') }} a {{ $b->fin->format($b->inicio->isSameDay($b->fin) ? 'H:i' : 'd/m/Y H:i') }}</p>
                                @if ($b->motivo)<p class="text-sm text-tinta-suave">{{ $b->motivo }}</p>@endif
                            </div>
                            <form method="POST" action="{{ route('gestion.bloqueos.destroy', [$p, $b]) }}">
                                @csrf @method('delete')
                                <button class="btn-texto btn-sm">Quitar</button>
                            </form>
                        </li>
                    @empty
                        <li class="px-5 py-5 text-sm text-tinta-suave">Sin ausencias registradas.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <aside class="space-y-6">
            <form method="POST" action="{{ route('gestion.horarios.store', $p) }}" class="tarjeta space-y-4 p-5">
                @csrf
                <h2 class="font-semibold">Agregar horario</h2>
                <fieldset>
                    <legend class="etiqueta">Días</legend>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach (\App\Models\Horario::DIAS as $n => $dia)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="dias[]" value="{{ $n }}" class="peer sr-only" @checked(in_array($n, old('dias', [])))>
                                <span class="flex h-11 items-center justify-center rounded-ctl border border-linea text-sm font-semibold peer-checked:border-petroleo peer-checked:bg-petroleo peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-petroleo">{{ mb_substr($dia, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('dias')" />
                </fieldset>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="hora_inicio" class="etiqueta">Desde</label>
                        <input id="hora_inicio" type="time" name="hora_inicio" value="{{ old('hora_inicio', '08:00') }}" class="campo dato" step="900" required>
                    </div>
                    <div>
                        <label for="hora_fin" class="etiqueta">Hasta</label>
                        <input id="hora_fin" type="time" name="hora_fin" value="{{ old('hora_fin', '12:00') }}" class="campo dato" step="900" required>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('hora_inicio')" />
                <x-input-error :messages="$errors->get('hora_fin')" />
                <button class="btn-primario w-full">Agregar horario</button>
                <p class="ayuda">Para un horario partido (mañana y tarde), agrega dos rangos el mismo día.</p>
            </form>

            <form method="POST" action="{{ route('gestion.bloqueos.store', $p) }}" class="tarjeta space-y-4 p-5">
                @csrf
                <h2 class="font-semibold">Registrar ausencia</h2>
                <p class="-mt-2 text-sm text-tinta-suave">Vacaciones, congreso o un permiso. Esas horas dejan de ofrecerse.</p>
                <div class="grid grid-cols-[1fr_110px] gap-3">
                    <div>
                        <label for="desde_fecha" class="etiqueta">Desde</label>
                        <input id="desde_fecha" type="date" name="desde_fecha" value="{{ old('desde_fecha', now()->toDateString()) }}" min="{{ now()->toDateString() }}" class="campo" required>
                    </div>
                    <div>
                        <label for="desde_hora" class="etiqueta">Hora</label>
                        <input id="desde_hora" type="time" name="desde_hora" value="{{ old('desde_hora', '07:00') }}" class="campo dato" required>
                    </div>
                    <div>
                        <label for="hasta_fecha" class="etiqueta">Hasta</label>
                        <input id="hasta_fecha" type="date" name="hasta_fecha" value="{{ old('hasta_fecha', now()->toDateString()) }}" min="{{ now()->toDateString() }}" class="campo" required>
                    </div>
                    <div>
                        <label for="hasta_hora" class="etiqueta">Hora</label>
                        <input id="hasta_hora" type="time" name="hasta_hora" value="{{ old('hasta_hora', '21:00') }}" class="campo dato" required>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('desde_fecha')" />
                <x-input-error :messages="$errors->get('hasta_fecha')" />
                <x-input-error :messages="$errors->get('hasta_hora')" />
                <div>
                    <label for="motivo" class="etiqueta">Motivo (opcional)</label>
                    <input id="motivo" name="motivo" value="{{ old('motivo') }}" class="campo" placeholder="ej. Vacaciones">
                </div>
                <button class="btn-secundario w-full">Registrar ausencia</button>
            </form>
        </aside>
    </div>
</x-app-layout>
