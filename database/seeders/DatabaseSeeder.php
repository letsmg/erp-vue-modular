<?php

namespace Database\Seeders;

use Modules\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roda SingleUserSeeder primeiro (cria admin se não existir)
        $this->call(SingleUserSeeder::class);

        // Roda UserSeeder (cria usuários adicionais)
        $this->call(UserSeeder::class);

        // Roda SupplierSeeder
        $this->call(SupplierSeeder::class);

        // Roda CategorySeeder
        $this->call(CategorySeeder::class);

        // Roda ClientSeeder
        $this->call(ClientSeeder::class);

        // Roda AddressSeeder
        $this->call(AddressSeeder::class);

        // Roda ProductSeeder
        $this->call(ProductSeeder::class);

        // Roda ShoppingCartSeeder
        $this->call(ShoppingCartSeeder::class);
    }
}
