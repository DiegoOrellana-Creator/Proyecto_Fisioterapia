<section>
    <h2 class="titulo-m">Datos personales</h2>
    <p class="mt-1 text-sm text-tinta-suave">El establecimiento usa tu celular y correo para avisarte cambios en tus citas.</p>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" :value="old('nombre', $user->nombre)" required autocomplete="given-name" />
                <x-input-error :messages="$errors->get('nombre')" />
            </div>
            <div>
                <x-input-label for="apellido" value="Apellido" />
                <x-text-input id="apellido" name="apellido" :value="old('apellido', $user->apellido)" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('apellido')" />
            </div>
            <div>
                <x-input-label for="telefono" value="Celular" />
                <x-text-input id="telefono" type="tel" name="telefono" :value="old('telefono', $user->telefono)" autocomplete="tel" />
                <x-input-error :messages="$errors->get('telefono')" />
            </div>
            <div>
                <x-input-label for="ci" value="Carnet de identidad" />
                <x-text-input id="ci" name="ci" :value="old('ci', $user->ci)" />
                <x-input-error :messages="$errors->get('ci')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Guardar datos</x-primary-button>
            @if (session('status') === 'profile-updated')<p class="text-sm text-estado-atendida">Guardado.</p>@endif
        </div>
    </form>
</section>
