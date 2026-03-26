<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Listar produtos
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Ver produto específico
     */
    public function view(User $user, Product $product): bool
    {
        return $user->isStaff();
    }

    /**
     * Criar produto
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Atualizar produto
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isStaff();
    }

    /**
     * Deletar produto
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Toggle (ativar/desativar / featured)
     */
    public function toggle(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}