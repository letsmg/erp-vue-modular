<?php

namespace App\Enums;

enum AccessLevel: int
{
    case OPERATOR = 0;
    case ADMIN = 1;
    case CLIENT = 2;

    /**
     * Verificações rápidas
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isOperator(): bool
    {
        return $this === self::OPERATOR;
    }

    public function isClient(): bool
    {
        return $this === self::CLIENT;
    }

    /**
     * Permissões (🔥 já preparando pro futuro)
     */
    public function canManageProducts(): bool
    {
        return in_array($this, [self::ADMIN, self::OPERATOR]);
    }

    public function canDelete(): bool
    {
        return $this === self::ADMIN;
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::ADMIN, self::OPERATOR]);
    }
}