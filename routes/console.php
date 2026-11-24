<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/*
 * Carga los datos de demostración solo si la base está vacía.
 * El script de arranque del contenedor lo ejecuta en cada despliegue sin riesgo:
 * después de la primera vez no vuelve a sembrar ni borra lo que cargaron los usuarios.
 */
Artisan::command('demo:sembrar', function () {
    if (User::query()->exists()) {
        $this->info('La base ya tiene usuarios: no se cargan datos de demostración.');

        return;
    }

    $this->call('db:seed', ['--force' => true]);
    $this->info('Datos de demostración cargados.');
})->purpose('Carga los datos de demostración si la base de datos está vacía');
