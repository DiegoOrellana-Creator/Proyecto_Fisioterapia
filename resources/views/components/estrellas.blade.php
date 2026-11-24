@props(['valor' => null, 'total' => null, 'tam' => 'h-4 w-4'])
@if ($valor)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
        <span class="inline-flex" aria-hidden="true">
            @for ($i = 1; $i <= 5; $i++)
                <svg class="{{ $tam }} {{ $i <= round($valor) ? 'text-[#C98A12]' : 'text-linea' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.8l2.5 5.2 5.7.8-4.1 4 1 5.6L10 14.7l-5.1 2.7 1-5.6-4.1-4 5.7-.8z"/></svg>
            @endfor
        </span>
        <span class="num text-sm font-semibold text-tinta">{{ number_format($valor, 1, ',', '') }}</span>
        @if (! is_null($total))
            <span class="text-sm text-tinta-suave">({{ $total }} {{ $total === 1 ? 'opinión' : 'opiniones' }})</span>
        @endif
        <span class="sr-only">{{ number_format($valor, 1, ',', '') }} de 5 estrellas</span>
    </span>
@else
    <span {{ $attributes->merge(['class' => 'text-sm text-tinta-suave']) }}>Sin opiniones todavía</span>
@endif
