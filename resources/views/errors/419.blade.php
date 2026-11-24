@component('errors.layout', ['codigo' => '419', 'titulo' => 'Tu sesión venció', 'explicacion' => 'Pasó demasiado tiempo sin actividad y, por seguridad, el formulario ya no es válido. Vuelve a la página y envíalo otra vez.'])
    @slot('acciones')
        <a href="{{ url()->previous() }}" class="btn-secundario">Volver a la página anterior</a>
    @endslot
@endcomponent
