<?php

namespace Database\Seeders;

use Modules\Product\Models\Product;
use Modules\Product\Models\ProductImage;
use Modules\Supplier\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Garante que a pasta de produtos existe
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $supplier = Supplier::first() ?? Supplier::create([
            'company_name' => 'Fornecedor Padrão',
            'email' => 'fornecedor@teste.com'
        ]);

        $this->command->info('Iniciando criacao de produtos e download de imagens...');

        Product::factory(20)->create([
            'supplier_id' => $supplier->id
        ])->each(function ($product) {

            // 1. Criar o SEO
            // meta_title e h1 são derivados do product->description (usado no frontend)
            $product->seo()->create([
                'meta_description' => "Compre agora " . $product->description . " com as melhores condições.",
                'meta_keywords'    => str_replace(' ', ', ', $product->description),
                'text1'            => "Descrição detalhada do produto " . $product->description,
                // text2, h2, etc., são nullables, então não precisam estar aqui.
            ]);

            // 2. Criar 3 imagens baixando da internet
            for ($i = 1; $i <= 3; $i++) {
                $imageName = 'prod_' . $product->id . '_' . $i . '.jpg';
                $imageUrl = "https://picsum.photos/640/480?random=" . rand(1, 10000);

                try {
                    $imageContent = file_get_contents($imageUrl);

                    if ($imageContent) {
                        // Baixa a imagem se não existir no storage
                        if (!Storage::disk('public')->exists('products/' . $imageName)) {
                            Storage::disk('public')->put('products/' . $imageName, $imageContent);
                        }

                        ProductImage::firstOrCreate(
                            ['product_id' => $product->id, 'order' => $i],
                            ['path' => $imageName]
                        );
                    }
                } catch (\Exception $e) {
                    $this->command->error("Falha ao baixar imagem para o produto {$product->id}");
                }
            }

            $this->command->comment("Produto {$product->id} criado com sucesso.");
        });

        $this->command->info('Seeder finalizado!');
    }
}