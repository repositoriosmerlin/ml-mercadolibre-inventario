<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cambio --- 10/09/2026: catálogo de permisos. Las filas no se escriben a
// mano: las siembra CatalogoDePermisos leyendo el middleware 'can' de las
// rutas, así el catálogo no puede desincronizarse del código.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('modulo')->index();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
