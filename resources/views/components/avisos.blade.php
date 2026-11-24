@props(['general' => true])
@if (session('ok'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-ctl border border-green-200 bg-green-50 px-4 py-3 text-[15px] text-tinta">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-estado-atendida" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"/></svg>
        <span>{{ session('ok') }}</span>
    </div>
@endif
@if ($errors->any() && $general)
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-ctl border border-red-200 bg-red-50 px-4 py-3 text-[15px] text-tinta">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-estado-cancelada" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.5v.5"/></svg>
        <div>
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                <p class="font-semibold">Revisa estos datos:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
