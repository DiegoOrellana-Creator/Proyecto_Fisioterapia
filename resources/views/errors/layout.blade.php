<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :titulo="$titulo" />
</head>
<body class="min-h-screen">
    <div class="mx-auto flex min-h-screen max-w-xl flex-col justify-center px-6 py-16">
        <x-marca class="mb-12 self-start" />
        <p class="dato text-sm font-semibold text-tinta-suave">Error {{ $codigo }}</p>
        <h1 class="titulo-l mt-2">{{ $titulo }}</h1>
        <p class="mt-3 text-[17px] leading-relaxed text-tinta-suave">{{ $explicacion }}</p>
        <div class="mt-8 flex flex-wrap gap-3">
            {{ $acciones }}
        </div>
    </div>
</body>
</html>
