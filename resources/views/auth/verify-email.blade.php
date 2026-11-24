<x-guest-layout titulo="Verifica tu correo">
    <h1 class="titulo-l">Verifica tu correo</h1>
    <p class="mt-2 text-[15px] text-tinta-suave">Te enviamos un enlace de verificación. Si no lo encuentras, revisa la carpeta de spam o pide uno nuevo.</p>

    @if (session('status') == 'verification-link-sent')
        <p class="mt-6 rounded-ctl border border-green-200 bg-green-50 px-4 py-3 text-sm">Enviamos un enlace nuevo a tu correo.</p>
    @endif

    <div class="mt-8 flex flex-wrap items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>Reenviar enlace</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-texto">Cerrar sesión</button>
        </form>
    </div>
</x-guest-layout>
