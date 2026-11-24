@component('errors.layout', ['codigo' => '500', 'titulo' => 'Algo falló en el servidor', 'explicacion' => 'No se guardó ningún cambio. Inténtalo otra vez; si se repite, avisa al establecimiento por teléfono o WhatsApp.'])
    @slot('acciones')
        <button type="button" onclick="location.reload()" class="btn-primario">Reintentar</button>
        <a href="{{ url('/') }}" class="btn-primario">Ir al inicio</a>
    @endslot
@endcomponent
