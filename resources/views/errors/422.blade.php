@component('errors.layout', ['codigo' => '422', 'titulo' => 'No se pudo hacer ese cambio', 'explicacion' => $exception->getMessage() ?: 'La cita cambió de estado mientras tanto. Vuelve a la página para ver su estado actual.'])
    @slot('acciones')
        <a href="{{ url()->previous() }}" class="btn-primario">Volver a la página anterior</a>
    @endslot
@endcomponent
