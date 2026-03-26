<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\AccessLevel;


class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(int $level)
    {
        return User::factory()->create(['access_level' => $level]);
    }

    /**
     * 1. LISTAGEM: Apenas Nível 0 ou 1 podem ver.
     */
    public function test_only_authenticated_users_with_level_0_or_1_can_access_products_index()
    {
        $admin = $this->createUser(1);
        $operator = $this->createUser(0);
        $guest = User::factory()->create([
            'access_level' => AccessLevel::CLIENT
        ]);

        $this->actingAs($admin)->get(route('products.index'))->assertStatus(200);
        $this->actingAs($operator)->get(route('products.index'))->assertStatus(200);
        
        // Se o seu middleware bloquear outros níveis:
        $this->actingAs($guest)->get(route('products.index'))->assertStatus(403);
    }

    /**
     * 2. ATIVAR/DESATIVAR (Toggle): Apenas Nível 1.
     */
    public function test_only_level_1_can_toggle_featured_status()
    {
        $admin = $this->createUser(1);
        $operator = $this->createUser(0);
        $product = Product::factory()->create(['is_featured' => false]);

        // Operador tenta ativar (Nível 0) -> Deve falhar
        $this->actingAs($operator)
            ->patch(route('products.toggle-featured', $product->id))
            ->assertStatus(403);

        // 1. Guardamos o resultado da requisição na variável $response
        $response = $this->actingAs($admin)
            ->patch(route('products.toggle-featured', $product->id));

        // 2. Agora podemos usar a variável para verificar o redirecionamento
        $response->assertRedirect();

        // 3. Verificamos se mudou no banco
        $this->assertTrue($product->refresh()->is_featured);
    }

    /**
     * 3. CADASTRO: Nível 0 só cadastra com is_active = 0 (ou forçado a 0).
     */
    public function test_level_0_can_only_create_inactive_products()
    {
        // 0. CRIAMOS O OPERADOR (Nível 0) - Adicione esta linha:
        $operator = $this->createUser(0);

        // 1. Geramos os dados base do Produto
        $productData = Product::factory()->make(['is_active' => true])->toArray();

        // 2. Adicionamos os campos de SEO
        $seoData = [
            'meta_title'       => 'Título de Teste',
            'meta_description' => 'Descrição de teste para o produto.',
            'meta_keywords'    => 'teste,laravel,seo',
            'h1'               => 'H1 de Teste',
            'text1'            => 'Texto longo de teste',
        ];

        // 3. Juntamos tudo e adicionamos a imagem
        $data = array_merge($productData, $seoData);
        $data['images'] = [\Illuminate\Http\UploadedFile::fake()->create('p.jpg', 100)];

        // 4. Fazemos o post (Agora o $operator existe!)
        $response = $this->actingAs($operator)->post(route('products.store'), $data);

        // 5. Validações
        $response->assertSessionHasNoErrors();
        
        $product = Product::latest('id')->first();
        $this->assertNotNull($product);
        $this->assertFalse((bool)$product->is_active);
    }

    /**
     * 4. EDIÇÃO: Nível 0 pode editar, mas não pode ativar.
     */
    public function test_level_0_cannot_activate_product_during_update()
    {
        $operator = $this->createUser(0);
        $product = Product::factory()->create(['is_active' => false]);

        $this->actingAs($operator)->patch(route('products.update', $product->id), [
            'description' => 'Descricao Alterada',
            'is_active' => true // Tentando burlar
        ]);

        $this->assertFalse($product->refresh()->is_active);
        $this->assertEquals('Descricao Alterada', $product->description);
    }

    /**
     * 5. DELETE: Apenas Nível 1.
     */
    public function test_only_level_1_can_delete_products()
    {
        $admin = $this->createUser(1);
        $operator = $this->createUser(0);
        $product = Product::factory()->create();

        // Operador tenta apagar
        $this->actingAs($operator)
            ->delete(route('products.destroy', $product->id))
            ->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id]);

        // Admin apaga
        $this->actingAs($admin)
            ->delete(route('products.destroy', $product->id))
            ->assertStatus(302); // Redirecionamento após delete

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}