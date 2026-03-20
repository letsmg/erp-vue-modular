<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getAllOrdered()
    {
        return User::orderBy('name')->get();
    }

    public function getNonAdmin()
    {
        return User::where('access_level', 0)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): void
    {
        $user->update($data);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}