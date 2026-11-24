<x-app-layout :titulo="$s->exists ? $s->nombre : 'Nuevo servicio'">
    <x-slot name="header">
        <a href="{{ route('admin.servicios.index') }}" class="text-sm font-medium text-petroleo hover:underline">Servicios</a>
        <h1 class="titulo-l mt-1">{{ $s->exists ? $s->nombre : 'Nuevo servicio' }}</h1>
    </x-slot>

    <form method="POST" action="{{ $s->exists ? route('admin.servicios.update', $s) : route('admin.servicios.store') }}" class="tarjeta max-w-3xl space-y-5 p-5 sm:p-6">
        @csrf
        @if ($s->exists) @method('put') @endif

        @if (auth()->user()->esAdminGeneral())
            <div>
                <x-input-label for="establecimiento_id" value="Establecimiento" />
                <select id="establecimiento_id" name="establecimiento_id" class="campo" required>
                    <option value="">Elige uno</option>
                    @foreach ($establecimientos as $e)<option value="{{ $e->id }}" @selected((int) old('establecimiento_id', $s->establecimiento_id) === $e->id)>{{ $e->nombre }}</option>@endforeach
                </select>
                <x-input-error :messages="$errors->get('establecimiento_id')" />
            </div>
        @else
            <input type="hidden" name="establecimiento_id" value="{{ auth()->user()->establecimiento_id }}">
        @endif

        <div class="grid gap-5 sm:grid-cols-[1fr_220px]">
            <div>
                <x-input-label for="nombre" value="Nombre del servicio" />
                <x-text-input id="nombre" name="nombre" :value="old('nombre', $s->nombre)" required placeholder="ej. Rehabilitación de rodilla" />
                <x-input-error :messages="$errors->get('nombre')" />
            </div>
            <div>
                <x-input-label for="categoria" value="Categoría" />
                <select id="categoria" name="categoria" class="campo" required>
                    @foreach (\App\Models\Servicio::CATEGORIAS as $c)<option @selected(old('categoria', $s->categoria) === $c)>{{ $c }}</option>@endforeach
                </select>
            </div>
        </div>
        <div>
            <x-input-label for="descripcion" value="Descripción" />
            <textarea id="descripcion" name="descripcion" rows="3" class="campo">{{ old('descripcion', $s->descripcion) }}</textarea>
            <p class="ayuda">Explica en lenguaje sencillo para qué sirve y a quién está dirigido.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <x-input-label for="duracion_min" value="Duración por sesión" />
                <div class="relative">
                    <input id="duracion_min" type="number" name="duracion_min" min="15" max="240" step="5" value="{{ old('duracion_min', $s->duracion_min) }}" class="campo num pr-14" required>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-tinta-suave">min</span>
                </div>
                <x-input-error :messages="$errors->get('duracion_min')" />
            </div>
            <div>
                <x-input-label for="precio" value="Precio por sesión" />
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-tinta-suave">Bs</span>
                    <input id="precio" type="number" name="precio" min="0" step="0.50" value="{{ old('precio', $s->precio) }}" class="campo dato pl-10 text-right" required>
                </div>
                <x-input-error :messages="$errors->get('precio')" />
            </div>
            <div>
                <x-input-label for="sesiones_sugeridas" value="Sesiones sugeridas" />
                <input id="sesiones_sugeridas" type="number" name="sesiones_sugeridas" min="1" max="30" value="{{ old('sesiones_sugeridas', $s->sesiones_sugeridas) }}" class="campo num" required>
                <x-input-error :messages="$errors->get('sesiones_sugeridas')" />
            </div>
        </div>

        <label class="flex min-h-[44px] items-center gap-3">
            <input type="checkbox" name="activo" value="1" class="h-5 w-5 rounded border-[#7E9194] text-petroleo focus:ring-petroleo" @checked(old('activo', $s->activo))>
            <span>Visible para los pacientes</span>
        </label>

        <div class="border-t border-linea pt-5">
            <x-primary-button>{{ $s->exists ? 'Guardar cambios' : 'Crear servicio' }}</x-primary-button>
        </div>
    </form>
</x-app-layout>
