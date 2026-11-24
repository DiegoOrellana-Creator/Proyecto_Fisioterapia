@php
    $serviciosPorEst = $establecimientos->mapWithKeys(fn ($e) => [$e->id => $e->servicios->map(fn ($s) => ['id' => $s->id, 'nombre' => $s->nombre])]);
    $seleccionados = collect(old('servicios', $p->exists ? $p->servicios->pluck('id')->all() : []))->map(fn ($v) => (int) $v);
@endphp
<x-app-layout :titulo="$p->exists ? $u->nombre_completo : 'Registrar profesional'">
    <x-slot name="header">
        <a href="{{ route('admin.profesionales.index') }}" class="text-sm font-medium text-petroleo hover:underline">Profesionales</a>
        <h1 class="titulo-l mt-1">{{ $p->exists ? $u->nombre_completo : 'Registrar profesional' }}</h1>
    </x-slot>

    <form method="POST" action="{{ $p->exists ? route('admin.profesionales.update', $p) : route('admin.profesionales.store') }}"
          x-data="{ est: '{{ old('establecimiento_id', $p->establecimiento_id) }}', porEst: @js($serviciosPorEst), sel: @js($seleccionados) }"
          class="max-w-3xl space-y-6">
        @csrf
        @if ($p->exists) @method('put') @endif

        <section class="tarjeta space-y-5 p-5 sm:p-6">
            <h2 class="titulo-m">Cuenta de acceso</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="nombre" value="Nombre" />
                    <x-text-input id="nombre" name="nombre" :value="old('nombre', $u->nombre)" required />
                    <x-input-error :messages="$errors->get('nombre')" />
                </div>
                <div>
                    <x-input-label for="apellido" value="Apellido" />
                    <x-text-input id="apellido" name="apellido" :value="old('apellido', $u->apellido)" required />
                    <x-input-error :messages="$errors->get('apellido')" />
                </div>
                <div>
                    <x-input-label for="email" value="Correo (para ingresar)" />
                    <x-text-input id="email" type="email" name="email" :value="old('email', $u->email)" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="password" :value="$p->exists ? 'Contraseña nueva (opcional)' : 'Contraseña inicial'" />
                    <x-text-input id="password" type="password" name="password" autocomplete="new-password" :required="! $p->exists" />
                    <p class="ayuda">{{ $p->exists ? 'Déjala vacía para no cambiarla.' : 'Mínimo 8 caracteres. El profesional puede cambiarla en su perfil.' }}</p>
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <div>
                    <x-input-label for="telefono" value="Celular" />
                    <x-text-input id="telefono" name="telefono" :value="old('telefono', $u->telefono)" />
                </div>
                <div>
                    <x-input-label for="ci" value="Carnet de identidad" />
                    <x-text-input id="ci" name="ci" :value="old('ci', $u->ci)" />
                    <x-input-error :messages="$errors->get('ci')" />
                </div>
            </div>
        </section>

        <section class="tarjeta space-y-5 p-5 sm:p-6">
            <h2 class="titulo-m">Perfil profesional</h2>
            @if (auth()->user()->esAdminGeneral())
                <div>
                    <x-input-label for="establecimiento_id" value="Establecimiento" />
                    <select id="establecimiento_id" name="establecimiento_id" x-model="est" class="campo" required>
                        <option value="">Elige uno</option>
                        @foreach ($establecimientos as $e)<option value="{{ $e->id }}">{{ $e->nombre }}</option>@endforeach
                    </select>
                    <x-input-error :messages="$errors->get('establecimiento_id')" />
                </div>
            @else
                <input type="hidden" name="establecimiento_id" value="{{ auth()->user()->establecimiento_id }}">
            @endif
            <div class="grid gap-5 sm:grid-cols-[1fr_180px_180px]">
                <div>
                    <x-input-label for="especialidad" value="Especialidad" />
                    <x-text-input id="especialidad" name="especialidad" :value="old('especialidad', $p->especialidad)" required placeholder="ej. Fisioterapia deportiva" />
                    <x-input-error :messages="$errors->get('especialidad')" />
                </div>
                <div>
                    <x-input-label for="matricula" value="Matrícula" />
                    <x-text-input id="matricula" name="matricula" :value="old('matricula', $p->matricula)" class="dato" />
                    <x-input-error :messages="$errors->get('matricula')" />
                </div>
                <div>
                    <x-input-label for="anios_experiencia" value="Años de experiencia" />
                    <x-text-input id="anios_experiencia" type="number" min="0" max="60" name="anios_experiencia" :value="old('anios_experiencia', $p->anios_experiencia ?? 0)" class="num" required />
                </div>
            </div>
            <div>
                <x-input-label for="biografia" value="Presentación" />
                <textarea id="biografia" name="biografia" rows="3" class="campo">{{ old('biografia', $p->biografia) }}</textarea>
            </div>

            <fieldset>
                <legend class="etiqueta">Servicios que atiende</legend>
                <template x-if="!est || !(porEst[est] || []).length">
                    <p class="rounded-ctl bg-lienzo px-4 py-3 text-sm text-tinta-suave" x-text="est ? 'Este establecimiento no tiene servicios. Créalos primero en Servicios.' : 'Elige el establecimiento para ver sus servicios.'"></p>
                </template>
                <div class="grid gap-2 sm:grid-cols-2">
                    <template x-for="s in (porEst[est] || [])" :key="s.id">
                        <label class="flex min-h-[44px] items-center gap-3 rounded-ctl border border-linea px-3 hover:border-petroleo">
                            <input type="checkbox" name="servicios[]" :value="s.id" x-model.number="sel" class="h-5 w-5 rounded border-[#7E9194] text-petroleo focus:ring-petroleo">
                            <span class="text-[15px]" x-text="s.nombre"></span>
                        </label>
                    </template>
                </div>
            </fieldset>

            <label class="flex min-h-[44px] items-center gap-3">
                <input type="checkbox" name="activo" value="1" class="h-5 w-5 rounded border-[#7E9194] text-petroleo focus:ring-petroleo" @checked(old('activo', $p->activo))>
                <span>Activo: puede ingresar y recibir reservas</span>
            </label>
        </section>

        <div class="flex gap-3">
            <x-primary-button>{{ $p->exists ? 'Guardar cambios' : 'Registrar y configurar horarios' }}</x-primary-button>
            @if ($p->exists)<a href="{{ route('gestion.horarios', $p) }}" class="btn-secundario">Horarios</a>@endif
        </div>
    </form>
</x-app-layout>
