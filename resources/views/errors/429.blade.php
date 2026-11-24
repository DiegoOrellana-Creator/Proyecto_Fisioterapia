@component('errors.layout', ['codigo' => '429', 'titulo' => 'Demasiados intentos seguidos', 'explicacion' => 'Espera un minuto antes de volver a intentarlo.'])
    @slot('acciones')
        <a href="{{ url()->previous() }}" class="btn-secundario">Volver a la página anterior</a>
    @endslot
@endcomponent
