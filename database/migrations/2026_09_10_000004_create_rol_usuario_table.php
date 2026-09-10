<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cambio --- 10/09/2026: pivote usuario <-> rol. La columna se llama user_id
// y no usuario_id porque apunta a la tabla 'users' del framework, que no
// vamos a renombrar.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->primary(['rol_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_usuario');
    }
};
