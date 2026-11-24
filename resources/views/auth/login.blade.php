<x-guest-layout titulo="Ingresar">
    <h1 class="titulo-l">Ingresa a tu cuenta</h1>
    <p class="mt-2 text-[15px] text-tinta-suave">Pacientes, profesionales y administradores usan la misma entrada.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nombre@correo.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="flex items-baseline justify-between">
                <x-input-label for="password" value="Contraseña" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-petroleo hover:underline" href="{{ route('password.request') }}">Olvidé mi contraseña</a>
                @endif
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="inline-flex min-h-[44px] items-center gap-2.5 text-[15px]">
            <input id="remember_me" type="checkbox" class="h-5 w-5 rounded border-[#7E9194] text-petroleo focus:ring-petroleo" name="remember">
            Mantener la sesión iniciada
        </label>

        <x-primary-button class="w-full">Ingresar</x-primary-button>
    </form>

    <p class="mt-8 text-[15px] text-tinta-suave">
        ¿Primera vez? <a href="{{ route('register') }}" class="enlace">Crea tu cuenta de paciente</a>
    </p>

    @if (app()->environment('local'))
        <details class="mt-10 rounded-ctl border border-dashed border-linea bg-white p-4 text-sm">
            <summary class="cursor-pointer font-semibold">Cuentas de demostración</summary>
            <p class="mt-2 text-tinta-suave">Las cuentas de prueba y su contraseña están documentadas en <code class="rounded bg-lienzo px-1">database/seeders/DatabaseSeeder.php</code> y en el README.</p>
        </details>
    @endif
</x-guest-layout>
