@php
    $colores = [
        ['Lienzo', '#EEF3F2', null, 'Fondo de página'],
        ['Superficie', '#FFFFFF', null, 'Tarjetas, campos y barras'],
        ['Línea', '#D3DFDE', null, 'Divisores. No para texto ni bordes de campo'],
        ['Borde de campo *', '#7E9194', '3,3:1', 'Borde de campos y casillas'],
        ['Texto', '#0F2A30', '15,1:1', 'Texto principal'],
        ['Texto secundario', '#4F6870', '5,9:1', 'Ayudas, metadatos'],
        ['Petróleo', '#0E5E66', '7,5:1', 'Marca, botón principal, foco. Blanco encima 7,5:1'],
        ['Petróleo presionado', '#0A4A51', '9,9:1', 'Hover y activo del botón principal'],
        ['Cinta', '#E5487A', '3,8:1', 'Solo la cinta decorativa. Nunca texto'],
        ['Disponible / atendida', '#23703F', '6,1:1', 'Ícono de badge, fondo #E1ECE4'],
        ['Solicitada / próximo', '#9A6A0B', '4,7:1', 'Solo ícono y borde; el texto va en #0F2A30'],
        ['Cancelada / error', '#9B3030', '7,3:1', 'Badge, error y cancelar'],
    ];
    $foco = 'ring-2 ring-petroleo ring-offset-2';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head titulo="Tokens de interfaz" />
    <meta name="robots" content="noindex">
</head>
<body class="bg-lienzo">
<div class="overflow-x-auto">
<main class="mx-auto flex w-[1440px] flex-col gap-14 px-20 py-16">

    {{-- Cabecera --}}
    <header class="flex items-end justify-between gap-10 border-b border-linea pb-8">
        <div class="flex flex-col gap-3">
            <p class="text-xl font-bold text-petroleo">FisioAgenda SCZ</p>
            <h1 class="text-[44px] font-bold leading-[50px] tracking-[-0.02em]">Tokens de interfaz: ficha de mostrador</h1>
            <p class="max-w-[720px] text-base leading-6 text-tinta-suave">Agenda clínica en petróleo y una cinta kinesiológica como único acento. Los mismos botones, campos y estados sirven al sitio público y al panel del establecimiento.</p>
        </div>
        <dl class="tarjeta grid grid-cols-[auto_auto] gap-x-6 gap-y-1 px-5 py-4 font-mono text-xs leading-[18px]">
            <dt class="text-tinta-suave">Versión</dt><dd>v2, 2026-11-22</dd>
            <dt class="text-tinta-suave">Tema</dt><dd>Solo claro</dd>
            <dt class="text-tinta-suave">Moneda</dt><dd>Bs, 2 decimales, coma</dd>
            <dt class="text-tinta-suave">Contraste</dt><dd>WCAG 2.1 AA</dd>
        </dl>
    </header>

    {{-- 01 Color --}}
    <section>
        <div class="flex items-baseline gap-4">
            <span class="font-mono text-[13px] font-semibold text-[#B02E58]">01</span>
            <h2 class="text-[26px] font-semibold leading-8">Color</h2>
            <p class="text-sm text-tinta-suave">Contraste medido sobre superficie #FFFFFF. * corregido para cumplir AA (antes #8A9C9F, 2,9:1)</p>
        </div>
        <div class="mt-5 grid grid-cols-6 gap-4">
            @foreach ($colores as [$nombre, $hex, $contraste, $uso])
                <div class="tarjeta overflow-hidden">
                    <div class="h-[72px] border-b border-linea" style="background: {{ $hex }}"></div>
                    <div class="flex flex-col gap-1 p-3">
                        <p class="text-sm font-semibold">{{ $nombre }}</p>
                        <p class="font-mono text-xs">{{ $hex }}@if ($contraste), {{ $contraste }}@endif</p>
                        <p class="text-xs leading-4 text-tinta-suave">{{ $uso }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 02 Tipografía --}}
    <section>
        <div class="flex items-baseline gap-4">
            <span class="font-mono text-[13px] font-semibold text-[#B02E58]">02</span>
            <h2 class="text-[26px] font-semibold leading-8">Tipografía</h2>
            <p class="text-sm text-tinta-suave">Instrument Sans para la interfaz. Spline Sans Mono con cifras tabulares para precio, hora y código</p>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-4">
            <div class="tarjeta px-6 py-2">
                @foreach ([
                    ['display · 54/57 · 700', 'text-[54px] leading-[57px] font-bold tracking-[-0.03em]', 'Tu sesión, reservada'],
                    ['título 1 · 28/34 · 700', 'text-[28px] leading-[34px] font-bold tracking-[-0.02em]', 'Centro Kinésico Equipetrol'],
                    ['título 2 · 20/28 · 600', 'text-xl leading-7 font-semibold', 'Servicios y precios'],
                    ['cuerpo · 15/22 · 400', 'text-[15px] leading-[22px]', 'Tu cita queda solicitada hasta que el establecimiento la confirme.'],
                    ['pequeño · 13/18 · 500', 'text-[13px] leading-[18px] font-medium text-tinta-suave', 'Lun a vie 7:00–20:00, sáb 8:00–12:00'],
                    ['etiqueta · 13/18 · 600', 'text-[13px] leading-[18px] font-semibold', 'Correo electrónico'],
                ] as [$rotulo, $clase, $muestra])
                    <div class="grid grid-cols-[170px_1fr] items-center gap-4 border-b border-linea py-4 last:border-0">
                        <span class="font-mono text-xs text-tinta-suave">{{ $rotulo }}</span>
                        <span class="{{ $clase }}">{{ $muestra }}</span>
                    </div>
                @endforeach
            </div>
            <div class="tarjeta px-6 py-2">
                @foreach ([
                    ['precio L · 28/34 · 600', 'text-[28px] leading-[34px] text-petroleo', 'Bs 150,00'],
                    ['precio M · 17/22 · 600', 'text-[17px] leading-[22px] text-petroleo', 'Bs 90,00'],
                    ['total · 22/28 · 600', 'text-[22px] leading-7', 'Bs 1.200,00'],
                    ['código · 13/18 · 500', 'text-[13px] leading-[18px] font-medium', 'FA-7K2Q9M, MP-4137'],
                ] as [$rotulo, $clase, $muestra])
                    <div class="grid grid-cols-[170px_1fr] items-center gap-4 border-b border-linea py-4">
                        <span class="font-mono text-xs text-tinta-suave">{{ $rotulo }}</span>
                        <span class="dato font-semibold {{ $clase }}">{{ $muestra }}</span>
                    </div>
                @endforeach
                <div class="grid grid-cols-[170px_1fr] items-center gap-4 py-4">
                    <span class="font-mono text-xs text-tinta-suave">hora · 14/20 · 600</span>
                    <div class="grid w-fit grid-cols-3 gap-1.5">
                        @foreach (['08:00', '08:30', '09:00', '14:30', '15:00', '15:30'] as $i => $h)
                            <span class="dato rounded-ctl border px-3 py-1.5 text-sm font-semibold {{ $i === 1 ? 'border-petroleo bg-petroleo text-white' : 'border-linea' }}">{{ $h }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 03 Espacio, radio y foco --}}
    <section>
        <div class="flex items-baseline gap-4">
            <span class="font-mono text-[13px] font-semibold text-[#B02E58]">03</span>
            <h2 class="text-[26px] font-semibold leading-8">Espacio, radio y foco</h2>
            <p class="text-sm text-tinta-suave">Base 4. El radio crece con el tamaño del elemento</p>
        </div>
        <div class="mt-5 grid grid-cols-3 gap-4">
            <div class="tarjeta px-6 py-5">
                <p class="text-sm font-semibold">Espaciado, base 4</p>
                <div class="mt-4 flex items-end gap-4">
                    @foreach ([4, 8, 12, 16, 24, 32, 48] as $n)
                        <div class="flex flex-col items-center gap-2">
                            <span class="block bg-petroleo/80" style="width: {{ $n }}px; height: {{ $n }}px"></span>
                            <span class="font-mono text-xs text-tinta-suave">{{ $n }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="tarjeta px-6 py-5">
                <p class="text-sm font-semibold">Radios</p>
                <div class="mt-4 flex items-end gap-6">
                    @foreach ([['h-11 w-16 rounded-ctl', 'control, 8'], ['h-11 w-16 rounded-tarjeta', 'tarjeta, 14'], ['h-7 w-16 rounded-full', 'píldora, 999']] as [$c, $t])
                        <div class="flex flex-col items-center gap-2">
                            <span class="block border border-[#7E9194] bg-lienzo {{ $c }}"></span>
                            <span class="font-mono text-xs text-tinta-suave">{{ $t }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="tarjeta px-6 py-5">
                <p class="text-sm font-semibold">Foco y área táctil</p>
                <div class="mt-4 flex h-11 items-center rounded-ctl border border-[#7E9194] bg-white px-3 text-[15px] {{ $foco }}">Foco visible</div>
                <p class="mt-4 text-[13px] leading-[18px] text-tinta-suave">Anillo 2 px #0E5E66, separación 2 px. Mínimo 44 × 44 px en todo control.</p>
            </div>
        </div>
    </section>

    {{-- 04 Botones --}}
    <section>
        <div class="flex items-baseline gap-4">
            <span class="font-mono text-[13px] font-semibold text-[#B02E58]">04</span>
            <h2 class="text-[26px] font-semibold leading-8">Botones</h2>
            <p class="text-sm text-tinta-suave">Alto 44 px. Un solo botón principal por paso</p>
        </div>
        <div class="tarjeta mt-5 px-6 py-2">
            <div class="grid grid-cols-[220px_repeat(4,1fr)_1.4fr] gap-4 border-b border-linea py-3 font-mono text-xs text-tinta-suave">
                <span>Variante</span><span>Reposo</span><span>Hover</span><span>Foco</span><span>Deshabilitado</span><span>Uso</span>
            </div>
            @foreach ([
                ['Principal', 'Enviar reserva', 'btn-primario', 'btn bg-petroleo-oscuro text-white', 'Enviar reserva, Confirmar, Agregar horario'],
                ['Secundario', 'Reprogramar', 'btn-secundario', 'btn bg-petroleo-claro text-petroleo border border-petroleo', 'Acción alternativa del mismo paso'],
                ['Texto', 'Quitar filtros', 'btn-texto', 'btn px-3 text-petroleo bg-petroleo-claro underline', 'Acciones de bajo peso, navegación'],
                ['Peligro', 'Cancelar cita', 'btn-peligro', 'btn bg-red-50 text-estado-cancelada border border-estado-cancelada', 'Cancelar, desactivar. Siempre con confirmación'],
            ] as [$variante, $texto, $base, $hover, $uso])
                <div class="grid grid-cols-[220px_repeat(4,1fr)_1.4fr] items-center gap-4 border-b border-linea py-4 last:border-0">
                    <span class="text-sm font-semibold">{{ $variante }}</span>
                    <span><span class="{{ $base }}">{{ $texto }}</span></span>
                    <span><span class="{{ $hover }}">{{ $texto }}</span></span>
                    <span><span class="{{ $base }} {{ $foco }}">{{ $texto }}</span></span>
                    <span><button type="button" class="{{ $base }}" disabled>{{ $texto }}</button></span>
                    <span class="text-[13px] text-tinta-suave">{{ $uso }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 05 Campos --}}
    <section>
        <div class="flex items-baseline gap-4">
            <span class="font-mono text-[13px] font-semibold text-[#B02E58]">05</span>
            <h2 class="text-[26px] font-semibold leading-8">Campos</h2>
            <p class="text-sm text-tinta-suave">Etiqueta siempre visible arriba. Alto 44 px. Error con ícono y texto</p>
        </div>
        <div class="tarjeta mt-5 grid grid-cols-4 gap-x-6 gap-y-7 p-6">
            <div>
                <span class="etiqueta">Correo electrónico</span>
                <input class="campo" placeholder="nombre@correo.com" tabindex="-1">
                <p class="ayuda">Reposo</p>
            </div>
            <div>
                <span class="etiqueta">Correo electrónico</span>
                <input class="campo border-petroleo ring-2 ring-petroleo/30" value="mariana.gutierrez@correo.com" tabindex="-1">
                <p class="ayuda">Foco</p>
            </div>
            <div>
                <span class="etiqueta">Contraseña</span>
                <input type="password" class="campo border-2 border-estado-cancelada" tabindex="-1">
                <p class="mt-1 flex items-center gap-1.5 text-xs text-estado-cancelada">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.5v.5"/></svg>
                    Ingresa tu contraseña.
                </p>
            </div>
            <div>
                <span class="etiqueta text-tinta-suave">Establecimiento</span>
                <input class="campo border-linea bg-lienzo text-tinta-suave" value="Centro Kinésico Equipetrol" disabled>
                <p class="ayuda">Deshabilitado</p>
            </div>
            <div>
                <span class="etiqueta">Buscar</span>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-tinta-suave" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input class="campo pl-10" placeholder="Lesión o tratamiento (ej. rodilla)" tabindex="-1">
                </div>
                <p class="ayuda">Buscador</p>
            </div>
            <div>
                <span class="etiqueta">Especialidad</span>
                <select class="campo" tabindex="-1"><option>Traumatología</option><option>Deportiva</option><option>Suelo pélvico</option></select>
                <p class="ayuda">Selector</p>
            </div>
            <div>
                <span class="etiqueta">Sesiones a reservar</span>
                <div class="flex h-11 w-[168px] items-center rounded-ctl border border-[#7E9194] bg-white">
                    <span class="flex h-full w-11 items-center justify-center text-xl font-semibold text-petroleo">−</span>
                    <span class="dato flex-1 text-center font-semibold">4</span>
                    <span class="flex h-full w-11 items-center justify-center text-xl font-semibold text-petroleo">+</span>
                </div>
                <p class="ayuda">Entero, mínimo 1, máximo 12</p>
            </div>
            <div>
                <span class="etiqueta">Precio por sesión</span>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-tinta-suave">Bs</span>
                    <input class="campo dato pl-10 text-right" value="150,00" tabindex="-1">
                </div>
                <p class="ayuda">Paso de 0,50</p>
            </div>
        </div>
    </section>

    {{-- 06 Estados --}}
    <section>
        <div class="flex items-baseline gap-4">
            <span class="font-mono text-[13px] font-semibold text-[#B02E58]">06</span>
            <h2 class="text-[26px] font-semibold leading-8">Estados</h2>
            <p class="text-sm text-tinta-suave">Siempre ícono + texto. El color nunca va solo. Texto en #0F2A30</p>
        </div>
        <div class="mt-5 grid grid-cols-4 gap-3.5">
            <div class="tarjeta flex flex-col gap-3 p-5">
                <p class="text-xs font-semibold text-tinta-suave">Catálogo público</p>
                <x-disponibilidad estado="hoy" texto="Libre hoy desde las 16:00" class="w-fit" />
                <x-disponibilidad estado="pronto" texto="Próximo libre: jueves 26, 08:00" class="w-fit" />
                <x-disponibilidad estado="sin" texto="Sin horarios esta semana" class="w-fit" />
                <p class="text-[13px] leading-[18px] text-tinta-suave">Primer horario libre de cualquier profesional en los próximos 7 días.</p>
            </div>
            <div class="tarjeta flex flex-col gap-3 p-5">
                <p class="text-xs font-semibold text-tinta-suave">Panel del profesional</p>
                @foreach ([['hoy', 'Libre hoy', '6 libres'], ['pronto', 'Agenda llena hoy', '0 / 9'], ['sin', 'Sin horario', 'no recibe']] as [$e, $t, $n])
                    <div class="flex items-center justify-between gap-2">
                        <x-disponibilidad :estado="$e" :texto="$t" />
                        <span class="dato text-xs text-tinta-suave">{{ $n }}</span>
                    </div>
                @endforeach
                <p class="text-[13px] leading-[18px] text-tinta-suave">Mismo badge; la cantidad va en monoespaciada a la derecha.</p>
            </div>
            <div class="tarjeta flex flex-col gap-3 p-5">
                <p class="text-xs font-semibold text-tinta-suave">Reserva</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (['solicitada', 'confirmada', 'reprogramada', 'cancelada'] as $e)<x-estado :estado="$e" />@endforeach
                </div>
                <p class="text-[13px] leading-[18px] text-tinta-suave">Solicitada y reprogramada esperan confirmación. Solo las activas ocupan la agenda.</p>
            </div>
            <div class="tarjeta flex flex-col gap-3 p-5">
                <p class="text-xs font-semibold text-tinta-suave">Asistencia</p>
                <div class="flex flex-wrap items-center gap-2">
                    <x-estado estado="atendida" />
                    <x-estado estado="no_asistio" />
                    <span class="dato rounded-ctl border border-linea px-2 py-0.5 text-xs">18,8 %</span>
                </div>
                <p class="text-[13px] leading-[18px] text-tinta-suave">Se registra cuando la cita ya empezó. La tasa de inasistencia va junto al estado.</p>
            </div>
        </div>
    </section>
</main>
</div>
</body>
</html>
