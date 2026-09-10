<?php

namespace App\Policies;

use App\Models\Calon;
use App\Models\User;

class CalonPolicy
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
        return true;
    }

    public function view(User $user, Calon $calon): bool
    {
        return (string) $user->desa_id === (string) $calon->desa_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Calon $calon): bool
    {
        return (string) $user->desa_id === (string) $calon->desa_id;
    }

    public function delete(User $user, Calon $calon): bool
    {
        return false;
    }
}
