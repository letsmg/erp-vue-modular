<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // --- ADICIONE ESTE BLOCO AQUI ---
        $this->command->warn('Limpando pasta de imagens antiga...');
        
        // Deleta todos os arquivos da pasta products dentro do disco public
        Storage::disk('public')->deleteDirectory('products');
        
        // Recria a pasta vazia
        Storage::disk('public')->makeDirectory('products');
        // --------------------------------

        $supplier = Supplier::first() ?? Supplier::create([
            'company_name' => 'Fornecedor Padrão',
            'email' => 'fornecedor@teste.com'
        ]);

        // Criar a pasta se não existir
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $this->command->info('Iniciando criacao de produtos e download de imagens...');

        Product::factory(20)->create([
            'supplier_id' => $supplier->id
        ])->each(function ($product) {
            
            // 1. Criar o SEO
            $product->seo()->create([
                'meta_title'       => $product->description,
                'slug'             => Str::slug($product->description) . '-' . $product->id,
                'meta_description' => "Compre agora " . $product->description,
                'h1'               => $product->description,
            ]);

            // 2. Criar 3 imagens baixando da internet
            for ($i = 1; $i <= 3; $i++) {
                $imageName = 'prod_' . $product->id . '_' . $i . '.jpg';
                $imageUrl = "https://picsum.photos/640/480?random=" . rand(1, 10000);

                try {
                    // Baixa a imagem
                    $imageContent = file_get_contents($imageUrl);
                    
                    if ($imageContent) {
                        // Salva no storage/app/public/products/
                        Storage::disk('public')->put('products/' . $imageName, $imageContent);

                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $imageName, // Caminho que o seu Vue já espera
                            'order'      => $i
                        ]);
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