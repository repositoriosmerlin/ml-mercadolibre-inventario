<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// cambio --- 10/09/2026: guarda la conexión con Mercado Libre. Es una fila por cuenta
// de vendedor, no por usuario de Merlin: la autorización la otorga el dueño de la
// tienda una sola vez y la comparten todos los usuarios internos.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meli_accounts', function (Blueprint $table) {
            $table->id();

            // El user_id que devuelve MELI al canjear el code. Es el seller_id y va en
            // casi todas las consultas, además de servir para saber a qué cuenta
            // pertenece una notificación entrante.
            $table->unsignedBigInteger('seller_id')->unique();
            $table->string('nickname')->nullable();

            // Cifrados en base de datos vía cast del modelo. Van como text porque el
            // cifrado de Laravel infla el valor bastante más allá de un varchar(255).
            $table->text('access_token');
            $table->text('refresh_token');

            // El access_token dura 6 horas. Se guarda el vencimiento para refrescar de
            // forma proactiva y no esperar al 401, que gasta una llamada y añade latencia.
            $table->timestamp('expires_at');
            $table->string('scopes')->nullable();

            // MELI revoca credenciales sin avisar (cambio de contraseña del vendedor,
            // desvinculación de dispositivos, 4 meses sin uso). Sin un estado explícito
            // los jobs reintentarían en bucle contra una cuenta muerta.
            $table->string('estado')->default('active');
            $table->text('ultimo_error')->nullable();
            $table->timestamp('ultima_sincronizacion_at')->nullable();

            // Quién apretó "Conectar", para rastrear la autorización.
            $table->foreignId('conectado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meli_accounts');
    }
};
