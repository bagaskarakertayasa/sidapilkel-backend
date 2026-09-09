<?php

namespace App\Policies;

use App\Models\Desa;
use App\Models\User;

class DesaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'ADMIN_PUSAT') {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Desa $desa): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Desa $desa): bool
    {
        return false;
    }

    public function delete(User $user, Desa $desa): bool
    {
        return false;
    }
}
