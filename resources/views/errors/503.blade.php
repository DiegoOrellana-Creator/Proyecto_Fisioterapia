@component('errors.layout', ['codigo' => '503', 'titulo' => 'Estamos en mantenimiento', 'explicacion' => 'La plataforma vuelve en unos minutos. Tus reservas siguen guardadas.'])
    @slot('acciones')
        <button type="button" onclick="location.reload()" class="btn-primario">Reintentar</button>
    @endslot
@endcomponent
