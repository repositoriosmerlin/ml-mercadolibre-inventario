<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cambio --- 10/09/2026: las cuentas no se eliminan, se deshabilitan.
//
// Se guarda la fecha y no un booleano porque "desde cuándo" es justo lo que
// se pregunta cuando alguien reclama que no puede entrar, y un booleano
// obliga a ir a buscarlo a la bitácora.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deshabilitado_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deshabilitado_at');
        });
    }
};
