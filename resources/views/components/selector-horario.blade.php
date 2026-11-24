{{-- Debe usarse dentro de un elemento con x-data="selectorHorario({...})" --}}
<fieldset>
    <legend class="etiqueta">Día</legend>
    <div class="flex items-center gap-2">
        <button type="button" class="btn-texto btn-sm shrink-0 px-2" @click="mover(-1)" :disabled="desde === 0" aria-label="Días anteriores">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
        </button>
        <div class="grid flex-1 grid-cols-7 gap-1.5">
            <template x-for="d in visibles" :key="d.iso">
                <button type="button" @click="elegirDia(d)" :disabled="!d.atiende"
                        :aria-pressed="fecha === d.iso" :aria-label="d.corto + ' ' + d.numero + ' de ' + d.mes + (d.atiende ? '' : ', no atiende')"
                        class="flex min-h-[60px] flex-col items-center justify-center rounded-ctl border text-center transition-colors"
                        :class="fecha === d.iso ? 'border-petroleo bg-petroleo text-white' : (d.atiende ? 'border-linea bg-white hover:border-petroleo' : 'border-transparent bg-transparent text-[#9AAAAE] line-through cursor-not-allowed')">
                    <span class="text-[11px] font-semibold" x-text="d.hoy ? 'hoy' : d.corto"></span>
                    <span class="num text-lg font-bold leading-tight" x-text="d.numero"></span>
                </button>
            </template>
        </div>
        <button type="button" class="btn-texto btn-sm shrink-0 px-2" @click="mover(1)" :disabled="desde + porPagina >= dias.length" aria-label="Días siguientes">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
        </button>
    </div>
    <p class="ayuda" x-text="'Mostrando ' + rotuloMes + '. En los días tachados no hay atención.'"></p>
</fieldset>

<fieldset class="mt-6">
    <legend class="etiqueta">Horario libre <span class="font-normal text-tinta-suave" x-text="fecha ? 'para el ' + fechaLegible : ''"></span></legend>

    <div aria-live="polite">
        {{-- Cargando --}}
        <div x-show="estado === 'cargando'" class="grid grid-cols-3 gap-2 sm:grid-cols-5" aria-label="Buscando horarios">
            <template x-for="i in 10"><div class="h-11 animate-pulse rounded-ctl bg-linea/60"></div></template>
        </div>

        {{-- Con horarios --}}
        <div x-show="estado === 'listo'" class="grid grid-cols-3 gap-2 sm:grid-cols-5">
            <template x-for="h in horarios" :key="h.inicio">
                <button type="button" class="chip-hora" @click="hora = h.inicio" :aria-pressed="hora === h.inicio" x-text="h.inicio"></button>
            </template>
        </div>

        {{-- Sin horarios --}}
        <div x-show="estado === 'vacio'" x-cloak class="rounded-ctl border border-dashed border-linea bg-lienzo px-4 py-6 text-center">
            <p class="font-semibold">No quedan horarios libres ese día.</p>
            <p class="mt-1 text-sm text-tinta-suave">Prueba con otro día de la lista.</p>
        </div>

        {{-- Error --}}
        <div x-show="estado === 'error'" x-cloak class="rounded-ctl border border-red-200 bg-red-50 px-4 py-5 text-center">
            <p class="font-semibold">No pudimos cargar los horarios.</p>
            <p class="mt-1 text-sm text-tinta-suave">Revisa tu conexión e inténtalo otra vez.</p>
            <button type="button" class="btn-secundario btn-sm mt-3" @click="cargar()">Reintentar</button>
        </div>
    </div>

    <input type="hidden" name="fecha" :value="fecha">
    <input type="hidden" name="hora" :value="hora">
</fieldset>
