<x-guest-layout titulo="Recuperar contraseña">
    <h1 class="titulo-l">Recupera tu contraseña</h1>
    <p class="mt-2 text-[15px] text-tinta-suave">Escribe el correo de tu cuenta y te enviaremos un enlace para crear una contraseña nueva.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <x-primary-button class="w-full">Enviar enlace</x-primary-button>
    </form>

    <p class="mt-8 text-[15px]"><a href="{{ route('login') }}" class="enlace">Volver a ingresar</a></p>
</x-guest-layout>
