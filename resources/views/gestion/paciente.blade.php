<x-app-layout :titulo="$paciente->nombre_completo">
    <x-slot name="header">
        <a href="{{ route('gestion.citas') }}" class="text-sm font-medium text-petroleo hover:underline">Citas</a>
        <h1 class="titulo-l mt-2">{{ $paciente->nombre_completo }}</h1>
        <p class="mt-1 text-[15px] text-tinta-suave">Historial de citas {{ auth()->user()->esProfesional() ? 'contigo' : 'en' }} {{ auth()->user()->esProfesional() ? '' : (auth()->user()->esAdminGeneral() ? 'todos los establecimientos' : auth()->user()->establecimiento->nombre) }}.</p>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="tarjeta overflow-x-auto">
            <table class="w-full min-w-[620px] text-left text-[15px]">
                <thead class="border-b border-linea bg-lienzo text-[13px] text-tinta-suave">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Fecha</th>
                        <th class="px-4 py-3 font-semibold">Servicio</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        <th class="px-4 py-3 font-semibold">Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($citas as $c)
                        <tr class="border-b border-linea align-top last:border-0">
                            <td class="dato whitespace-nowrap px-4 py-3"><a href="{{ route('gestion.citas.show', $c) }}" class="hover:text-petroleo">{{ $c->fecha->format('d/m/Y') }}</a><br><span class="text-sm text-tinta-suave">{{ substr($c->hora_inicio, 0, 5) }}</span></td>
                            <td class="px-4 py-3">{{ $c->servicio->nombre }}<p class="text-sm text-tinta-suave">{{ $c->profesional->user->nombre_completo }}</p></td>
                            <td class="px-4 py-3"><x-estado :estado="$c->estado" /></td>
                            <td class="px-4 py-3 text-sm text-tinta-suave">{{ \Illuminate\Support\Str::limit($c->nota_profesional ?? $c->motivo_cancelacion, 80) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <aside class="tarjeta p-5 lg:self-start">
            <h2 class="font-semibold">Asistencia</h2>
            <div class="mt-3">@include('gestion._resumen-paciente', ['resumen' => $resumen])</div>
            <dl class="mt-5 space-y-2 border-t border-linea pt-4 text-[15px]">
                @if ($paciente->telefono)<div><dt class="text-sm text-tinta-suave">Celular</dt><dd class="dato">{{ $paciente->telefono }}</dd></div>@endif
                <div><dt class="text-sm text-tinta-suave">Correo</dt><dd class="break-all">{{ $paciente->email }}</dd></div>
            </dl>
            <p class="mt-4 text-xs text-tinta-suave">Información personal de uso interno. Solo la ven los profesionales y administradores del establecimiento donde el paciente reservó.</p>
        </aside>
    </div>
</x-app-layout>
