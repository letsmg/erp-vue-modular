<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class SingleUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => '1@1.com'],
            [
                'name' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('Mudar@123'),
                'access_level' => 1,
                'is_active' => true,
            ]
        );
        $user = User::updateOrCreate(
            ['email' => '2@1.com'],
            [
                'name' => 'Padrão',
                'password' => \Illuminate\Support\Facades\Hash::make('Mudar@123'),
                'access_level' => 0,
                'is_active' => true,
            ]
        );
        $user = User::updateOrCreate(
            ['email' => '3@1.com'],
            [
                'name' => 'Cliente a implementar',
                'password' => \Illuminate\Support\Facades\Hash::make('Mudar@123'),
                'access_level' => 2,
                'is_active' => true,
            ]
        );


        $this->command->info("Usuário {$user->email} verificado/criado!");
    }
}