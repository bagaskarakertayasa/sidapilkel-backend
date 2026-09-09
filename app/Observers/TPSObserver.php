<?php

namespace App\Observers;

use App\Models\TPS;
use Illuminate\Support\Facades\Cache;

class TPSObserver
{
    public function saved(TPS $tps): void
    {
        $this->clearRekapCache();
    }

    public function deleted(TPS $tps): void
    {
        $this->clearRekapCache();
    }

    public function restored(TPS $tps): void
    {
        $this->clearRekapCache();
    }

    public function forceDeleted(TPS $tps): void
    {
        $this->clearRekapCache();
    }

    private function clearRekapCache(): void
    {
        Cache::forget('rekap:all:v1');
    }
}
