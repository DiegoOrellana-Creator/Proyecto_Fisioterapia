<x-app-layout titulo="Servicios y precios">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Servicios y precios</h1>
                <p class="mt-1 text-[15px] text-tinta-suave">La duración define cuánto tiempo ocupa cada reserva en la agenda.</p>
            </div>
            <a href="{{ route('admin.servicios.create') }}" class="btn-primario">Nuevo servicio</a>
        </div>
    </x-slot>

    @if (auth()->user()->esAdminGeneral())
        <form method="get" class="mb-4 flex items-center gap-2">
            <label for="establecimiento" class="text-sm text-tinta-suave">Establecimiento</label>
            <select id="establecimiento" name="establecimiento" class="campo w-auto min-w-[240px]" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach ($establecimientos as $e)<option value="{{ $e->id }}" @selected(request()->integer('establecimiento') === $e->id)>{{ $e->nombre }}</option>@endforeach
            </select>
        </form>
    @endif

    @if ($servicios->isEmpty())
        <div class="tarjeta px-6 py-12 text-center">
            <p class="font-semibold">Todavía no hay servicios.</p>
            <p class="mt-1 text-sm text-tinta-suave">Sin servicios, los pacientes no pueden reservar.</p>
            <a href="{{ route('admin.servicios.create') }}" class="btn-primario mt-4">Crear el primero</a>
        </div>
    @else
        <div class="tarjeta overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-[15px]">
                <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Servicio</th>
                        <th class="px-4 py-3 font-semibold">Categoría</th>
                        <th class="px-4 py-3 text-right font-semibold">Duración</th>
                        <th class="px-4 py-3 text-right font-semibold">Precio</th>
                        <th class="px-4 py-3 text-right font-semibold">Profesionales</th>
                        <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicios as $s)
                        <tr class="border-b border-linea last:border-0 {{ $s->activo ? '' : 'text-tinta-suave' }}">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $s->nombre }} @unless ($s->activo)<span class="text-sm">(oculto)</span>@endunless</p>
                                @if (auth()->user()->esAdminGeneral())<p class="text-sm text-tinta-suave">{{ $s->establecimiento->nombre }}</p>@endif
                            </td>
                            <td class="px-4 py-3">{{ $s->categoria }}</td>
                            <td class="num px-4 py-3 text-right">{{ $s->duracion_min }} min</td>
                            <td class="dato px-4 py-3 text-right font-semibold">{{ $s->precio_formateado }}</td>
                            <td class="num px-4 py-3 text-right {{ $s->profesionales_count ? '' : 'font-semibold text-estado-falta' }}">{{ $s->profesionales_count ?: 'Ninguno' }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.servicios.edit', $s) }}" class="btn-secundario btn-sm">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
