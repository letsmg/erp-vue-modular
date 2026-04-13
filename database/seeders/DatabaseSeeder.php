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
        // Só roda os seeders se as tabelas estiverem vazias
        if (\Modules\User\Models\User::count() == 0) {
            $this->call(SingleUserSeeder::class);
        }

        if (\Modules\User\Models\User::count() < 6) {
            $this->call(UserSeeder::class);
        }

        if (\Modules\Supplier\Models\Supplier::count() == 0) {
            $this->call(SupplierSeeder::class);
        }

        if (\App\Models\Category::count() == 0) {
            $this->call(CategorySeeder::class);
        }

        if (\Modules\Client\Models\Client::count() == 0) {
            $this->call(ClientSeeder::class);
        }

        if (\App\Models\Address::count() == 0) {
            $this->call(AddressSeeder::class);
        }

        if (\Modules\Product\Models\Product::count() == 0) {
            $this->call(ProductSeeder::class);
        }

        if (\App\Models\ShoppingCart::count() == 0) {
            $this->call(ShoppingCartSeeder::class);
        }
    }
}
