@props(['titulo' => null, 'descripcion' => 'Encuentra establecimientos de fisioterapia en Santa Cruz de la Sierra, compara servicios y precios, y reserva tu sesión en línea.'])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="{{ $descripcion }}">
<title>{{ $titulo ? $titulo.' | ' : '' }}{{ config('app.name') }}</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230E5E66'/%3E%3Crect x='6' y='13' width='20' height='6' rx='2' fill='%23E5487A' transform='rotate(-35 16 16)'/%3E%3Crect x='6' y='13' width='20' height='6' rx='2' fill='%23fff' opacity='.85' transform='rotate(35 16 16)'/%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|spline-sans-mono:500,600&display=swap" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
