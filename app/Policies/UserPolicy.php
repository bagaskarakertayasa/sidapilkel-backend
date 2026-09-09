<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'ADMIN_PUSAT') {
            return true;
        }
        return false;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdminPusat();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdminPusat();
    }

    public function create(User $user): bool
    {
        return $user->isAdminPusat();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdminPusat();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdminPusat() && $user->id !== $model->id;
    }

    public function toggleStatus(User $user, User $model): bool
    {
        return $user->isAdminPusat() && $user->id !== $model->id;
    }

    public function updatePassword(User $user, User $model): bool
    {
        return $user->isAdminPusat();
    }
}
