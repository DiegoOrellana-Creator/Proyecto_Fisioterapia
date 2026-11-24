<x-app-layout titulo="Usuarios">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="titulo-l">Usuarios</h1>
                <p class="mt-1 text-[15px] text-tinta-suave">Un usuario desactivado no puede ingresar ni reservar.</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}" class="btn-primario">Nuevo administrador</a>
        </div>
    </x-slot>

    <form method="get" class="tarjeta mb-4 grid gap-3 p-4 sm:grid-cols-[1fr_200px_auto]">
        <label class="block"><span class="sr-only">Buscar</span><input type="search" name="q" value="{{ $q }}" class="campo" placeholder="Nombre, correo o CI"></label>
        <label class="block"><span class="sr-only">Rol</span>
            <select name="rol" class="campo">
                <option value="">Todos los roles</option>
                <option value="paciente" @selected($rol === 'paciente')>Pacientes</option>
                <option value="profesional" @selected($rol === 'profesional')>Profesionales</option>
                <option value="admin" @selected($rol === 'admin')>Administradores</option>
            </select>
        </label>
        <button class="btn-primario">Filtrar</button>
    </form>

    <div class="tarjeta overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-[15px]">
            <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                <tr>
                    <th class="px-4 py-3 font-semibold">Nombre</th>
                    <th class="px-4 py-3 font-semibold">Rol</th>
                    <th class="px-4 py-3 text-right font-semibold">Asistencias / faltas</th>
                    <th class="px-4 py-3 font-semibold">Estado</th>
                    <th class="px-4 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $u)
                    <tr class="border-b border-linea last:border-0">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $u->nombre_completo }}</p>
                            <p class="text-sm text-tinta-suave">{{ $u->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            {{ ['paciente' => 'Paciente', 'profesional' => 'Profesional', 'admin' => 'Administrador'][$u->rol] ?? $u->rol }}
                            @if ($u->esAdmin())<p class="text-sm text-tinta-suave">{{ $u->establecimiento?->nombre ?? 'General' }}</p>@endif
                        </td>
                        <td class="num px-4 py-3 text-right">@if ($u->esPaciente()){{ $u->atendidas }} / <span class="{{ $u->faltas ? 'font-semibold text-estado-falta' : '' }}">{{ $u->faltas }}</span>@else<span class="text-tinta-suave">—</span>@endif</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex h-7 items-center rounded-full border px-2.5 text-[13px] font-semibold {{ $u->activo ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">{{ $u->activo ? 'Activo' : 'Desactivado' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.usuarios.toggle', $u) }}" onsubmit="return confirm('{{ $u->activo ? '¿Desactivar a '.$u->nombre_completo.'? No podrá ingresar.' : '¿Activar a '.$u->nombre_completo.'?' }}')">
                                    @csrf @method('patch')
                                    <button class="{{ $u->activo ? 'btn-peligro' : 'btn-secundario' }} btn-sm">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-tinta-suave">Ningún usuario coincide con la búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $usuarios->links() }}</div>
</x-app-layout>
