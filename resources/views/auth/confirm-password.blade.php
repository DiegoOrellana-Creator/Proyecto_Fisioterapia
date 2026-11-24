<x-guest-layout titulo="Confirmar contraseña">
    <h1 class="titulo-l">Confirma tu contraseña</h1>
    <p class="mt-2 text-[15px] text-tinta-suave">Es una sección protegida. Escribe tu contraseña para continuar.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <x-primary-button class="w-full">Continuar</x-primary-button>
    </form>
</x-guest-layout>
