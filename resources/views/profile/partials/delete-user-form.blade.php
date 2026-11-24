<section>
    <h2 class="titulo-m">Eliminar cuenta</h2>
    <p class="mt-1 text-sm text-tinta-suave">Se borran tu cuenta y tu historial de citas. No se puede deshacer.</p>

    <x-danger-button class="mt-5" type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Eliminar mi cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="titulo-m">¿Eliminar tu cuenta?</h2>
            <p class="mt-2 text-[15px] text-tinta-suave">Se borrarán tus datos y tu historial de citas. Escribe tu contraseña para confirmar.</p>

            <div class="mt-5">
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" name="password" type="password" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Conservar mi cuenta</x-secondary-button>
                <x-danger-button>Eliminar definitivamente</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
