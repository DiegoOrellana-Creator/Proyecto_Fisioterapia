@props(['messages'])
@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-estado-cancelada space-y-1 mt-1.5']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5"><svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.5v.5"/></svg>{{ $message }}</li>
        @endforeach
    </ul>
@endif
