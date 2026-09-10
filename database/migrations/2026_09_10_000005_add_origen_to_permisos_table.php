<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cambio --- 10/09/2026: distingue el permiso que declara una ruta del que se
// da de alta a mano desde el panel.
//
// Importa porque son cosas distintas: el de ruta lo protege el middleware
// 'can' y basta con asignarlo; el manual solo hace algo si alguien lo
// consulta explícitamente en una vista o un servicio. Sin la marca, la
// pantalla no puede advertirlo y quedan casillas que aparentan proteger.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos', function (Blueprint $table) {
            $table->string('origen', 10)->default('ruta')->after('modulo');
        });
    }

    public function down(): void
    {
        Schema::table('permisos', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
