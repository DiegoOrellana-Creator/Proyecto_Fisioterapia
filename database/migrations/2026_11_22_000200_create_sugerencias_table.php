<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Establecimientos que los pacientes piden incorporar porque no están en el catálogo. */
    public function up(): void
    {
        Schema::create('sugerencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('establecimiento', 120);
            $table->string('zona', 80)->nullable();
            $table->string('contacto', 120)->nullable();
            $table->text('comentario')->nullable();
            $table->boolean('revisada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sugerencias');
    }
};
