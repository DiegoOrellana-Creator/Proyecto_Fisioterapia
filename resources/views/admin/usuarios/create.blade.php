<x-app-layout titulo="Nuevo administrador">
    <x-slot name="header">
        <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-medium text-petroleo hover:underline">Usuarios</a>
        <h1 class="titulo-l mt-1">Nuevo administrador</h1>
        <p class="mt-1 text-[15px] text-tinta-suave">Los pacientes se registran solos; los profesionales se dan de alta en Profesionales.</p>
    </x-slot>

    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="tarjeta max-w-2xl space-y-5 p-5 sm:p-6">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" :value="old('nombre')" required />
                <x-input-error :messages="$errors->get('nombre')" />
            </div>
            <div>
                <x-input-label for="apellido" value="Apellido" />
                <x-text-input id="apellido" name="apellido" :value="old('apellido')" required />
                <x-input-error :messages="$errors->get('apellido')" />
            </div>
            <div>
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="telefono" value="Celular" />
                <x-text-input id="telefono" name="telefono" :value="old('telefono')" />
            </div>
        </div>
        <div>
            <x-input-label for="establecimiento_id" value="Establecimiento a cargo" />
            <select id="establecimiento_id" name="establecimiento_id" class="campo">
                <option value="">Ninguno: administrador general</option>
                @foreach ($establecimientos as $e)<option value="{{ $e->id }}" @selected((int) old('establecimiento_id') === $e->id)>{{ $e->nombre }}</option>@endforeach
            </select>
            <p class="ayuda">El administrador de un establecimiento solo ve y edita la información de ese establecimiento.</p>
        </div>
        <div>
            <x-input-label for="password" value="Contraseña inicial" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="border-t border-linea pt-5">
            <x-primary-button>Crear administrador</x-primary-button>
        </div>
    </form>
</x-app-layout>
