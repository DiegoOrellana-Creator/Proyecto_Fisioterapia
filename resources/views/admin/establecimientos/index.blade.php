<x-app-layout titulo="Establecimientos">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Establecimientos</h1>
                <p class="mt-1 text-[15px] text-tinta-suave">Los establecimientos inactivos no aparecen en el sitio público.</p>
            </div>
            <a href="{{ route('admin.establecimientos.create') }}" class="btn-primario">Nuevo establecimiento</a>
        </div>
    </x-slot>

    <div class="tarjeta overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-[15px]">
            <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                <tr>
                    <th class="px-4 py-3 font-semibold">Nombre</th>
                    <th class="px-4 py-3 font-semibold">Zona</th>
                    <th class="px-4 py-3 text-right font-semibold">Profesionales</th>
                    <th class="px-4 py-3 text-right font-semibold">Servicios</th>
                    <th class="px-4 py-3 text-right font-semibold">Citas</th>
                    <th class="px-4 py-3 font-semibold">Estado</th>
                    <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($establecimientos as $e)
                    <tr class="border-b border-linea last:border-0">
                        <td class="px-4 py-3 font-medium">{{ $e->nombre }}</td>
                        <td class="px-4 py-3">{{ $e->zona }}</td>
                        <td class="num px-4 py-3 text-right">{{ $e->profesionales_count }}</td>
                        <td class="num px-4 py-3 text-right">{{ $e->servicios_count }}</td>
                        <td class="num px-4 py-3 text-right">{{ $e->citas_count }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex h-7 items-center rounded-full border px-2.5 text-[13px] font-semibold {{ $e->activo ? 'border-green-200 bg-green-50' : 'border-linea bg-lienzo text-tinta-suave' }}">{{ $e->activo ? 'Publicado' : 'Oculto' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('establecimientos.show', $e) }}" class="btn-texto btn-sm" target="_blank">Ver</a>
                            <a href="{{ route('admin.establecimientos.edit', $e) }}" class="btn-secundario btn-sm">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
