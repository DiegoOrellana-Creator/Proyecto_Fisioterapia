<x-app-layout :titulo="$e->exists ? $e->nombre : 'Nuevo establecimiento'">
    <x-slot name="header">
        @if (auth()->user()->esAdminGeneral())
            <a href="{{ route('admin.establecimientos.index') }}" class="text-sm font-medium text-petroleo hover:underline">Establecimientos</a>
        @endif
        <h1 class="titulo-l mt-1">{{ $e->exists ? $e->nombre : 'Nuevo establecimiento' }}</h1>
        @if ($e->exists)
            <p class="mt-1 text-[15px] text-tinta-suave">Así se ve en el sitio: <a class="enlace" href="{{ route('establecimientos.show', $e) }}" target="_blank">página pública</a>.</p>
        @endif
    </x-slot>

    <form method="POST" action="{{ $e->exists ? route('admin.establecimientos.update', $e) : route('admin.establecimientos.store') }}" class="tarjeta max-w-3xl space-y-5 p-5 sm:p-6">
        @csrf
        @if ($e->exists) @method('put') @endif

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" :value="old('nombre', $e->nombre)" required />
            <x-input-error :messages="$errors->get('nombre')" />
        </div>
        <div>
            <x-input-label for="descripcion" value="Descripción" />
            <textarea id="descripcion" name="descripcion" rows="4" class="campo">{{ old('descripcion', $e->descripcion) }}</textarea>
            <p class="ayuda">Qué tratamientos ofrecen, a quién atienden y qué equipamiento tienen.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-[1fr_220px]">
            <div>
                <x-input-label for="direccion" value="Dirección" />
                <x-text-input id="direccion" name="direccion" :value="old('direccion', $e->direccion)" required />
                <x-input-error :messages="$errors->get('direccion')" />
            </div>
            <div>
                <x-input-label for="zona" value="Zona" />
                <input id="zona" name="zona" list="zonas" value="{{ old('zona', $e->zona) }}" class="campo" required>
                <datalist id="zonas">@foreach (\App\Models\Establecimiento::ZONAS as $z)<option value="{{ $z }}">@endforeach</datalist>
                <x-input-error :messages="$errors->get('zona')" />
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <x-input-label for="telefono" value="Teléfono fijo" />
                <x-text-input id="telefono" name="telefono" :value="old('telefono', $e->telefono)" />
            </div>
            <div>
                <x-input-label for="whatsapp" value="WhatsApp" />
                <x-text-input id="whatsapp" name="whatsapp" :value="old('whatsapp', $e->whatsapp)" placeholder="ej. 70812233" />
            </div>
            <div>
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" type="email" name="email" :value="old('email', $e->email)" />
                <x-input-error :messages="$errors->get('email')" />
            </div>
        </div>
        <div>
            <x-input-label for="horario_texto" value="Horario de atención (texto para el público)" />
            <x-text-input id="horario_texto" name="horario_texto" :value="old('horario_texto', $e->horario_texto)" placeholder="ej. Lun a vie 8:00–19:00, sáb 8:00–12:00" />
            <p class="ayuda">Es informativo. Las reservas usan el horario de cada profesional.</p>
        </div>

        @if (auth()->user()->esAdminGeneral())
            <label class="flex min-h-[44px] items-center gap-3">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" class="h-5 w-5 rounded border-[#7E9194] text-petroleo focus:ring-petroleo" @checked(old('activo', $e->activo))>
                <span>Publicado en el sitio</span>
            </label>
        @else
            <input type="hidden" name="activo" value="{{ $e->activo ? 1 : 0 }}">
        @endif

        <div class="flex flex-wrap gap-3 border-t border-linea pt-5">
            <x-primary-button>{{ $e->exists ? 'Guardar cambios' : 'Crear establecimiento' }}</x-primary-button>
            @if ($e->exists)
                <a href="{{ route('admin.servicios.index', ['establecimiento' => $e->id]) }}" class="btn-secundario">Servicios</a>
                <a href="{{ route('admin.profesionales.index') }}" class="btn-secundario">Profesionales</a>
            @endif
        </div>
    </form>
</x-app-layout>
