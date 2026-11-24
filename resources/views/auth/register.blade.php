<x-guest-layout titulo="Crear cuenta">
    <h1 class="titulo-l">Crea tu cuenta de paciente</h1>
    <p class="mt-2 text-[15px] text-tinta-suave">Con tu cuenta reservas sesiones, ves tu historial y cancelas o cambias tus citas sin llamar.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" :value="old('nombre')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('nombre')" />
            </div>
            <div>
                <x-input-label for="apellido" value="Apellido" />
                <x-text-input id="apellido" name="apellido" :value="old('apellido')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('apellido')" />
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="telefono" value="Celular" />
                <x-text-input id="telefono" type="tel" name="telefono" :value="old('telefono')" required autocomplete="tel" placeholder="ej. 76012345" />
                <p class="ayuda">El establecimiento lo usa si necesita avisarte un cambio.</p>
                <x-input-error :messages="$errors->get('telefono')" />
            </div>
            <div>
                <x-input-label for="ci" value="Carnet de identidad (opcional)" />
                <x-text-input id="ci" name="ci" :value="old('ci')" />
                <x-input-error :messages="$errors->get('ci')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
                <p class="ayuda">Al menos 8 caracteres.</p>
                <x-input-error :messages="$errors->get('password')" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Repite la contraseña" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <p class="text-sm text-tinta-suave">Tu historial de citas solo lo ven tú y los establecimientos donde reservas.</p>

        <x-primary-button class="w-full">Crear cuenta</x-primary-button>
    </form>

    <p class="mt-8 text-[15px] text-tinta-suave">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="enlace">Ingresa</a>
    </p>
</x-guest-layout>
