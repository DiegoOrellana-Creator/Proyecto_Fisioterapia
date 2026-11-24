{{--
    Estado de disponibilidad. Siempre ícono + texto; el color nunca va solo. Texto en tinta.
    Uso: <x-disponibilidad :cuando="$carbon|null" />  o  <x-disponibilidad estado="hoy|pronto|sin" texto="..." />
--}}
@props(['cuando' => false, 'estado' => null, 'texto' => null])
@php
    if ($cuando !== false) {
        if ($cuando === null) {
            $estado = 'sin';
            $texto = 'Sin horarios esta semana';
        } elseif ($cuando->isToday()) {
            $estado = 'hoy';
            $texto = 'Libre hoy desde las '.$cuando->format('H:i');
        } else {
            $estado = 'pronto';
            $texto = 'Próximo libre: '.($cuando->isTomorrow() ? 'mañana' : $cuando->translatedFormat('l j')).', '.$cuando->format('H:i');
        }
    }
    [$clase, $icono, $colorIcono] = [
        'hoy'    => ['bg-[#E1ECE4] border-[#9CC2AA]', 'M5 12l4 4 10-10', 'text-estado-atendida'],
        'pronto' => ['bg-[#F4EAD8] border-[#D4B07A]', 'M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'text-estado-solicitada'],
        'sin'    => ['bg-[#F3E2DF] border-[#CC9A93]', 'M6 6l12 12M18 6L6 18', 'text-estado-cancelada'],
    ][$estado] ?? ['bg-white border-linea', 'M12 12h.01', 'text-tinta-suave'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex h-7 items-center gap-1.5 rounded-full border px-2.5 text-[13px] font-semibold text-tinta whitespace-nowrap first-letter:uppercase $clase"]) }}>
    <svg class="h-3.5 w-3.5 shrink-0 {{ $colorIcono }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icono }}"/></svg>
    <span>{{ $texto }}</span>
</span>
