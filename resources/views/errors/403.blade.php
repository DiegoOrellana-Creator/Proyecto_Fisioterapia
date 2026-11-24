@component('errors.layout', ['codigo' => '403', 'titulo' => 'No tienes acceso a esta sección', 'explicacion' => $exception->getMessage() ?: 'Tu cuenta no tiene permiso para ver esta página. Si crees que es un error, pide acceso al administrador del establecimiento.'])
    @slot('acciones')
        <a href="{{ url('/') }}" class="btn-primario">Ir al inicio</a>
        <a href="{{ url()->previous() }}" class="btn-secundario">Volver a la página anterior</a>
    @endslot
@endcomponent
