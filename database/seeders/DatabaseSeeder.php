<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cuenta inicial para entrar al sistema.
        // CAMBIAR LA CONTRASEÑA antes de usar esto fuera de local.
        User::firstOrCreate(
            ['email' => 'admin@merlin.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // cambio --- 10/09/2026: va después de la cuenta inicial a propósito,
        // porque el seeder de roles le asigna el rol al primer usuario.
        $this->call(RolesYPermisosSeeder::class);
    }
}
