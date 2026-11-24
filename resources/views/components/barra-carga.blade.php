{{-- Estado de carga: aparece mientras el navegador espera la siguiente página --}}
<div x-data="{ cargando: false }"
     @beforeunload.window="cargando = true"
     @pageshow.window="cargando = false"
     x-show="cargando" x-cloak
     class="fixed inset-x-0 top-0 z-[60] h-1 overflow-hidden bg-petroleo-claro"
     role="progressbar" aria-label="Cargando la página">
    <div class="barra-carga h-full w-1/3 bg-petroleo"></div>
</div>
