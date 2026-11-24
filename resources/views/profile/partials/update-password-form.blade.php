<section>
    <h2 class="titulo-m">Contraseña</h2>
    <p class="mt-1 text-sm text-tinta-suave">Usa una contraseña larga que no uses en otros sitios.</p>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="update_password_password" value="Contraseña nueva" />
                <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" />
            </div>
            <div>
                <x-input-label for="update_password_password_confirmation" value="Repite la contraseña nueva" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Cambiar contraseña</x-primary-button>
            @if (session('status') === 'password-updated')<p class="text-sm text-estado-atendida">Contraseña cambiada.</p>@endif
        </div>
    </form>
</section>
