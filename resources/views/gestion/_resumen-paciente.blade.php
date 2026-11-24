{{-- Variables: $resumen --}}
<dl class="grid grid-cols-3 gap-2 text-center">
    <div class="rounded-ctl bg-green-50 px-2 py-3">
        <dt class="text-xs text-tinta-suave">Asistencias</dt>
        <dd class="num text-xl font-bold">{{ $resumen['atendidas'] }}</dd>
    </div>
    <div class="rounded-ctl bg-orange-50 px-2 py-3">
        <dt class="text-xs text-tinta-suave">Inasistencias</dt>
        <dd class="num text-xl font-bold">{{ $resumen['inasistencias'] }}</dd>
    </div>
    <div class="rounded-ctl bg-lienzo px-2 py-3">
        <dt class="text-xs text-tinta-suave">Canceladas</dt>
        <dd class="num text-xl font-bold">{{ $resumen['canceladas'] }}</dd>
    </div>
</dl>
@if (! is_null($resumen['tasa']))
    <p class="mt-3 text-sm text-tinta-suave">
        Faltó a <span class="num font-semibold text-tinta">{{ number_format($resumen['tasa'], 1, ',', '') }} %</span> de sus citas ya ocurridas.
        @if ($resumen['inasistencias'] >= 2)
            <span class="mt-1 block font-semibold text-estado-falta">Conviene confirmar la cita por teléfono.</span>
        @endif
    </p>
@endif
