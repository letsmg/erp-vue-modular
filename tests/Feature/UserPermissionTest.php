<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Pode usar seeders se quiser dados adicionais
        //$this->seed();
    }

    public function test_admin_pode_acessar_dashboard()
    {
        $admin = User::factory()->create([
            'access_level' => 1,
            'is_active' => true
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_tela_de_login_esta_acessivel()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_usuario_nao_autenticado_e_redirecionado_para_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_pode_fazer_logout()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('logout'));
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** --- TESTES DE PERMISSÃO (ADMIN / USUÁRIO COMUM) --- **/

    public function test_admin_pode_acessar_lista_de_usuarios()
    {
        $admin = User::factory()->create(['access_level' => 1]);
        $response = $this->actingAs($admin)->get(route('users.index'));
        $response->assertStatus(200);
    }

    public function test_usuario_comum_pode_visualizar_usuarios_nivel_0()
    {
        // Cria o usuário comum que fará a requisição
        $user = User::factory()->create(['access_level' => 0]);

        // Cria outros usuários nível 0 no banco
        $outroUsuario = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)->get(route('users.index'));

        // Espera 200 porque usuários comuns podem ver outros nível 0
        $response->assertStatus(200);

        // Confirma que os usuários retornados incluem o outro usuário nível 0
        $response->assertSee($outroUsuario->name);
    }

    public function test_usuario_comum_nao_pode_deletar_nivel_1()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $admin = User::factory()->create(['access_level' => 1]);

        $response = $this->actingAs($user)->delete(route('users.destroy', $admin));

        $response->assertStatus(403); // Não pode deletar admins
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_usuario_comum_pode_resetar_senha_outro_nivel_0()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $outroUsuario = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)->patch(route('users.reset', $outroUsuario));

        // Espera 200 porque usuários nível 0 podem resetar senha de outros nível 0
        $response->assertStatus(200);
    }

    public function test_admin_pode_cadastrar_usuario()
    {
        $admin = User::factory()->create(['access_level' => 1]);
        
        $senhaForte = 'Senha@Forte123';

        $novoUsuario = [
            'name' => 'Clone',
            'email' => 'clone@teste.com',
            'password' => $senhaForte,
            'password_confirmation' => $senhaForte,
            'access_level' => 0,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $novoUsuario);

        if ($response->status() !== 302) {
            dump($response->getSession()->get('errors')->getMessages());
        }

        $response->assertRedirect(route('users.index'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'clone@teste.com',
            'is_active' => 1
        ]);
    }

    public function test_usuario_comum_nao_pode_deletar_ninguem()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $alvo = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)->delete(route('users.destroy', $alvo));

        $response->assertStatus(403); // Nível 0 não pode deletar ninguém
        $this->assertDatabaseHas('users', ['id' => $alvo->id]);
    }
}