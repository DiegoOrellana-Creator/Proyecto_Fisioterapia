@component('errors.layout', ['codigo' => '404', 'titulo' => 'No encontramos esta página', 'explicacion' => 'El enlace puede estar mal escrito, o el establecimiento o profesional ya no publica su agenda.'])
    @slot('acciones')
        <a href="{{ route('establecimientos.index') }}" class="btn-primario">Buscar establecimientos</a>
        <a href="{{ url('/') }}" class="btn-secundario">Ir al inicio</a>
    @endslot
@endcomponent
