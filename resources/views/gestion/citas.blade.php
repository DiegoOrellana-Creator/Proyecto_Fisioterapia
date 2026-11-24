<x-app-layout titulo="Citas y asistencias">
    <x-slot name="header">
        <h1 class="titulo-l">Citas y asistencias</h1>
        <p class="mt-1 text-[15px] text-tinta-suave">Busca citas, confirma reservas y registra si el paciente asistió.</p>
    </x-slot>

    <form method="get" class="tarjeta grid gap-3 p-4 md:grid-cols-6">
        <div class="md:col-span-2">
            <label for="q" class="etiqueta">Paciente o código</label>
            <input id="q" type="search" name="q" value="{{ $filtros['q'] ?? '' }}" class="campo" placeholder="Nombre, CI o FA-…">
        </div>
        <div>
            <label for="estado" class="etiqueta">Estado</label>
            <select id="estado" name="estado" class="campo">
                <option value="">Todos</option>
                @foreach (\App\Models\Cita::ETIQUETAS as $k => $v)<option value="{{ $k }}" @selected(($filtros['estado'] ?? '') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        @if (auth()->user()->esAdmin())
            <div>
                <label for="profesional" class="etiqueta">Profesional</label>
                <select id="profesional" name="profesional" class="campo">
                    <option value="">Todos</option>
                    @foreach ($profesionales as $p)<option value="{{ $p->id }}" @selected((int) ($filtros['profesional'] ?? 0) === $p->id)>{{ $p->user->nombre_completo }}</option>@endforeach
                </select>
            </div>
        @endif
        <div>
            <label for="desde" class="etiqueta">Desde</label>
            <input id="desde" type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="campo">
        </div>
        <div>
            <label for="hasta" class="etiqueta">Hasta</label>
            <input id="hasta" type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="campo">
        </div>
        <div class="flex items-end gap-2 md:col-span-6">
            <button class="btn-primario">Filtrar</button>
            @if (array_filter($filtros))<a href="{{ route('gestion.citas') }}" class="btn-texto">Quitar filtros</a>@endif
            <p class="num ml-auto text-sm text-tinta-suave">{{ $citas->total() }} {{ $citas->total() === 1 ? 'cita' : 'citas' }}</p>
        </div>
    </form>

    @if ($citas->isEmpty())
        <div class="tarjeta mt-6 px-6 py-12 text-center">
            <p class="font-semibold">No hay citas con esos filtros.</p>
            <a href="{{ route('gestion.citas') }}" class="btn-secundario mt-4">Ver todas</a>
        </div>
    @else
        <div class="tarjeta mt-6 overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-[15px]">
                <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Fecha</th>
                        <th class="px-4 py-3 font-semibold">Paciente</th>
                        <th class="px-4 py-3 font-semibold">Servicio</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        <th class="px-4 py-3 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($citas as $c)
                        <tr class="border-b border-linea align-top last:border-0">
                            <td class="num whitespace-nowrap px-4 py-3">
                                {{ $c->fecha->format('d/m/Y') }}<br>
                                <span class="text-sm text-tinta-suave">{{ $c->rango_hora }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('gestion.pacientes.show', $c->paciente) }}" class="font-medium hover:text-petroleo">{{ $c->paciente->nombre_completo }}</a>
                                <p class="dato text-sm text-tinta-suave">{{ $c->codigo }}</p>
                            </td>
                            <td class="px-4 py-3">
                                {{ $c->servicio->nombre }}
                                @if (auth()->user()->esAdmin())<p class="text-sm text-tinta-suave">{{ $c->profesional->user->nombre_completo }}</p>@endif
                            </td>
                            <td class="px-4 py-3"><x-estado :estado="$c->estado" /></td>
                            <td class="px-4 py-3">@include('gestion._acciones', ['c' => $c])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $citas->links() }}</div>
    @endif
</x-app-layout>
