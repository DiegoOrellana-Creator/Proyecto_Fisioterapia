<x-app-layout titulo="Mi perfil">
    <x-slot name="header">
        <h1 class="titulo-l">Mi perfil</h1>
        <p class="mt-1 text-[15px] text-tinta-suave">Datos de contacto, contraseña{{ $user->profesional ? ' y la información que ven los pacientes' : '' }}.</p>
    </x-slot>

    <div class="grid max-w-3xl gap-6">
        <section class="tarjeta p-6 sm:p-8">@include('profile.partials.update-profile-information-form')</section>

        @if ($user->profesional)
            <section class="tarjeta p-6 sm:p-8">
                <h2 class="titulo-m">Perfil profesional</h2>
                <p class="mt-1 text-sm text-tinta-suave">Aparece en tu <a class="enlace" href="{{ route('profesionales.show', $user->profesional) }}">perfil público</a>.</p>
                <form method="post" action="{{ route('profile.profesional') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('patch')
                    <div class="grid gap-5 sm:grid-cols-[1fr_180px]">
                        <div>
                            <x-input-label for="especialidad" value="Especialidad" />
                            <x-text-input id="especialidad" name="especialidad" :value="old('especialidad', $user->profesional->especialidad)" required />
                            <x-input-error :messages="$errors->get('especialidad')" />
                        </div>
                        <div>
                            <x-input-label for="anios_experiencia" value="Años de experiencia" />
                            <x-text-input id="anios_experiencia" type="number" min="0" max="60" name="anios_experiencia" :value="old('anios_experiencia', $user->profesional->anios_experiencia)" required class="num" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="biografia" value="Presentación" />
                        <textarea id="biografia" name="biografia" rows="4" class="campo">{{ old('biografia', $user->profesional->biografia) }}</textarea>
                        <p class="ayuda">Cuenta en pocas líneas a quién atiendes y cómo trabajas.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <x-primary-button>Guardar perfil profesional</x-primary-button>
                        @if (session('status') === 'profesional-updated')<p class="text-sm text-estado-atendida">Guardado.</p>@endif
                    </div>
                </form>
            </section>
        @endif

        <section class="tarjeta p-6 sm:p-8">@include('profile.partials.update-password-form')</section>
        <section class="tarjeta p-6 sm:p-8">@include('profile.partials.delete-user-form')</section>
    </div>
</x-app-layout>
