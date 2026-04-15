<?php

namespace Modules\Product\Database\Factories;

use Modules\Product\Models\Product;
use Modules\Supplier\Models\Supplier;
use App\Models\Category;
use App\Models\Seo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'supplier_id'    => Supplier::factory(),
            'category_id'    => Category::factory(),
            'title'          => ucfirst($this->faker->words(3, true)),
            'subtitle'       => $this->faker->sentence(),
            'description'    => $this->faker->paragraph(),
            'features'       => $this->faker->paragraph(),
            'brand'          => $this->faker->company(),
            'model'          => $this->faker->bothify('??-###'),
            'size'           => $this->faker->randomElement(['P', 'M', 'G', 'GG']),
            'collection'     => 'Coleção ' . $this->faker->word(),
            'gender'         => $this->faker->randomElement(['Masculino', 'Feminino', 'Unissex']),
            'cost_price'     => $this->faker->randomFloat(2, 50, 150),
            'sale_price'     => $this->faker->randomFloat(2, 200, 500),
            'barcode'        => $this->faker->ean13(),
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'is_active'      => true,
            'is_featured'    => $this->faker->boolean(30),
            'weight'         => $this->faker->randomFloat(2, 0.1, 5),
            'width'          => $this->faker->numberBetween(10, 100),
            'height'         => $this->faker->numberBetween(10, 100),
            'length'         => $this->faker->numberBetween(10, 100),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $product->seo()->create([
                'meta_description' => "Compre " . $product->title . " com as melhores condições.",
                'meta_keywords'    => implode(',', $this->faker->words(5)),
            ]);
        });
    }
}
