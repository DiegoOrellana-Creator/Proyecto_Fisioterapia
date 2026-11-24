<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Módulo de establecimientos
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('slug', 140)->unique();
            $table->text('descripcion')->nullable();
            $table->string('direccion', 200);
            $table->string('zona', 80)->index();
            $table->string('telefono', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('horario_texto', 120)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Un administrador puede estar a cargo de un establecimiento (null = administrador general)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('establecimiento_id')->nullable()->after('rol')
                ->constrained('establecimientos')->nullOnDelete();
        });

        // Módulo de profesionales
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->string('matricula', 40)->nullable()->unique();
            $table->string('especialidad', 120);
            $table->text('biografia')->nullable();
            $table->unsignedTinyInteger('anios_experiencia')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Módulo de servicios
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('categoria', 60)->index();
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('duracion_min');
            $table->decimal('precio', 10, 2);
            $table->unsignedTinyInteger('sesiones_sugeridas')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('profesional_servicio', function (Blueprint $table) {
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->primary(['profesional_id', 'servicio_id']);
        });

        // Módulo de horarios y disponibilidad
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana'); // 1 = lunes, 7 = domingo (ISO-8601)
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
            $table->index(['profesional_id', 'dia_semana']);
        });

        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('motivo', 150)->nullable();
            $table->timestamps();
        });

        // Módulo de reservas y citas
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('paciente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            // solicitada | confirmada | reprogramada | cancelada | atendida | no_asistio
            $table->string('estado', 20)->default('solicitada')->index();
            $table->decimal('precio', 10, 2);
            $table->unsignedTinyInteger('numero_sesion')->default(1);
            $table->unsignedTinyInteger('total_sesiones')->default(1);
            $table->string('grupo', 20)->nullable()->index(); // agrupa las sesiones de un mismo tratamiento
            $table->text('nota_paciente')->nullable();
            $table->text('nota_profesional')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();
            $table->index(['profesional_id', 'fecha']);
        });

        Schema::create('historial_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->string('detalle', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Valoración y retroalimentación de pacientes
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->unique()->constrained('citas')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
            $table->foreignId('establecimiento_id')->constrained('establecimientos')->cascadeOnDelete();
            $table->unsignedTinyInteger('puntuacion'); // 1 a 5
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
        Schema::dropIfExists('historial_citas');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('bloqueos');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('profesional_servicio');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('profesionales');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('establecimiento_id');
        });
        Schema::dropIfExists('establecimientos');
    }
};
