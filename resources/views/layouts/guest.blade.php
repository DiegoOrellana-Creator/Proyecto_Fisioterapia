<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :titulo="$titulo ?? 'Ingresar'" />
</head>
<body class="min-h-screen">
    <div class="grid min-h-screen lg:grid-cols-[1fr_minmax(0,560px)]">
        {{-- Panel lateral con la marca --}}
        <aside class="relative hidden overflow-hidden bg-petroleo-oscuro text-white lg:block">
            <span class="cinta left-[-40px] top-[22%] w-[420px] -rotate-[28deg]" aria-hidden="true"></span>
            <span class="cinta left-[120px] top-[30%] w-[360px] rotate-[18deg] !bg-[#2BA7B5]" aria-hidden="true"></span>
            <div class="relative flex h-full flex-col justify-between p-12">
                <a href="{{ route('inicio') }}" class="inline-flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-9" />
                    <span class="text-lg font-bold tracking-[-0.02em]">FisioAgenda SCZ</span>
                </a>
                <div class="max-w-md">
                    <p class="text-[2rem] font-bold leading-tight tracking-[-0.02em]">Tu agenda de fisioterapia, sin llamadas ni mensajes perdidos.</p>
                    <p class="mt-4 text-[15px] leading-relaxed text-white/75">Los pacientes ven horarios libres y reservan. Los establecimientos confirman, registran asistencias y tienen el historial de cada paciente en un mismo lugar.</p>
                </div>
            </div>
        </aside>

        <main class="flex flex-col justify-center px-6 py-12 sm:px-12">
            <div class="mx-auto w-full max-w-md">
                <x-marca class="mb-10 lg:hidden" />
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
