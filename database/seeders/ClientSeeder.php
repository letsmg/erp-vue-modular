<?php

namespace Database\Seeders;

use Modules\Client\Models\Client;
use Modules\User\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = 'Mudar@123';

        // Criar 10 usuários clientes com padrão cli@1.com, cli@2.com...
        for ($i = 1; $i <= 10; $i++) {
            $email = "cli@$i.com";
            
            // Verifica se o usuário já existe para evitar erro de Unique
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => "Cliente Teste $i",
                        'email' => $email,
                        'password' => $password,
                        'access_level' => 2, // CLIENT
                        'is_active' => true,
                    ]
                );
            }

            // Garante que o cliente associado ao usuário também exista
            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => "Cliente Teste $i",
                    'document_type' => fake()->randomElement(['CPF', 'CNPJ']),
                    'document_number' => fake()->numerify(
                        fake()->randomElement(['###########', '##############'])
                    ),
                    'state_registration' => fake()->optional(0.7)->numerify('#########'),
                    'municipal_registration' => fake()->optional(0.5)->numerify('#########'),
                    'contributor_type' => fake()->randomElement([1, 2, 9]),
                    'is_active' => true,
                ]
            );
        }

        // Criar alguns clientes sem usuário (para testes de prospecção/vendas diretas)
        for ($i = 1; $i <= 5; $i++) {
            Client::firstOrCreate(
                ['document_number' => fake()->numerify(fake()->randomElement(['###########', '##############']))],
                [
                    'user_id' => null,
                    'is_active' => true,
                    'name' => "Cliente Prospecção $i",
                    'document_type' => fake()->randomElement(['CPF', 'CNPJ']),
                ]
            );
        }
    }
}
