{{-- Acciones rápidas sobre una cita. Variables: $c (Cita) --}}
<div class="flex flex-wrap items-center gap-2">
    @if (in_array($c->estado, ['solicitada', 'reprogramada']) && ! $c->yaPaso())
        <form method="POST" action="{{ route('gestion.citas.confirmar', $c) }}">
            @csrf
            <button class="btn-primario btn-sm">Confirmar</button>
        </form>
    @endif
    @if ($c->admiteAsistencia())
        <form method="POST" action="{{ route('gestion.citas.asistencia', $c) }}">
            @csrf
            <input type="hidden" name="asistio" value="1">
            <button class="btn-primario btn-sm">Asistió</button>
        </form>
        <form method="POST" action="{{ route('gestion.citas.asistencia', $c) }}">
            @csrf
            <input type="hidden" name="asistio" value="0">
            <button class="btn-secundario btn-sm">No asistió</button>
        </form>
    @endif
    <a href="{{ route('gestion.citas.show', $c) }}" class="btn-texto btn-sm">Detalle</a>
</div>
