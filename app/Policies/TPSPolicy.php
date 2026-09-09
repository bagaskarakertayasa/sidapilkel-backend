<?php

namespace App\Policies;

use App\Models\TPS;
use App\Models\User;

class TPSPolicy
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

    public function view(User $user, TPS $tps): bool
    {
        return (string) $user->desa_id === (string) $tps->desa_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TPS $tps): bool
    {
        return (string) $user->desa_id === (string) $tps->desa_id;
    }

    public function delete(User $user, TPS $tps): bool
    {
        return (string) $user->desa_id === (string) $tps->desa_id;
    }
}
