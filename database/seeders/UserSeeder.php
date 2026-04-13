<?php

namespace Database\Seeders;

use Modules\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Para os usuários normais aleatórios, podemos manter a factory,
        // mas é recomendável só rodar se a tabela estiver vazia para evitar lentidão
        if (User::count() < 5) {
            for ($i = 1; $i <= 5; $i++) {
                User::firstOrCreate(
                    ['email' => "user$i@teste.com"],
                    [
                        'name' => "Usuário Teste $i",
                        'password' => Hash::make('Mudar@123'),
                        'access_level' => fake()->randomElement([0, 2]),
                        'is_active' => true,
                    ]
                );
            }
        }

        // 2. O ADMIN (O ponto onde deu o erro)
        // Usamos updateOrCreate para evitar o erro de "Duplicate Entry"
        User::updateOrCreate(
            ['email' => 'admin@teste.com'], // Busca por este campo
            [
                'name' => 'Admin',
                'password' => Hash::make('Mudar@123'),
                'access_level' => 1,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}