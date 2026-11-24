@props(['estado'])
@php
    $estilos = [
        'solicitada'   => ['bg-amber-50 border-amber-300', 'M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'Solicitada'],
        'confirmada'   => ['bg-petroleo-claro border-[#8FBDBA]', 'M5 12l4 4 10-10', 'Confirmada'],
        'reprogramada' => ['bg-indigo-50 border-indigo-200', 'M4 12a8 8 0 0114-5.3M20 12a8 8 0 01-14 5.3M18 3v4h-4M6 21v-4h4', 'Reprogramada'],
        'cancelada'    => ['bg-red-50 border-red-200', 'M6 6l12 12M18 6L6 18', 'Cancelada'],
        'atendida'     => ['bg-green-50 border-green-200', 'M5 12l4 4 10-10', 'Atendida'],
        'no_asistio'   => ['bg-orange-50 border-orange-200', 'M12 8v4.5M12 16v.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'No asistió'],
    ];
    [$clase, $icono, $texto] = $estilos[$estado] ?? ['bg-white border-linea', 'M12 12h.01', $estado];
    $color = [
        'solicitada' => 'text-estado-solicitada', 'confirmada' => 'text-estado-confirmada',
        'reprogramada' => 'text-estado-reprogramada', 'cancelada' => 'text-estado-cancelada',
        'atendida' => 'text-estado-atendida', 'no_asistio' => 'text-estado-falta',
    ][$estado] ?? 'text-tinta-suave';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full border text-[13px] font-semibold text-tinta whitespace-nowrap $clase"]) }}>
    <svg class="h-3.5 w-3.5 {{ $color }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icono }}"/></svg>
    {{ $texto }}
</span>
