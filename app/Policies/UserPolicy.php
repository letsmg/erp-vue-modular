<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\AccessLevel;

class UserPolicy
{
    /**
     * Ver lista de usuários
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff(); // ADMIN ou OPERATOR
    }

    /**
     * Ver um usuário específico
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Criar usuário
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Atualizar usuário
     */
    public function update(User $user, User $model): bool
    {
        // Admin pode tudo
        if ($user->isAdmin()) {
            return true;
        }

        // Operador só pode editar a si mesmo
        return $user->id === $model->id;
    }

    /**
     * Deletar usuário
     */
    public function delete(User $user, User $model): bool
    {
        // Não pode deletar a si mesmo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Alterar status (ativar/desativar)
     */
    public function toggleStatus(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Resetar senha
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}