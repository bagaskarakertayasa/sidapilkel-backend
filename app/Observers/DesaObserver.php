<?php

namespace App\Observers;

use App\Models\Desa;
use Illuminate\Support\Facades\Cache;

class DesaObserver
{
    public function saved(Desa $desa): void
    {
        Cache::forget('rekap:all:v1');
    }

    public function deleted(Desa $desa): void
    {
        Cache::forget('rekap:all:v1');
    }

    public function restored(Desa $desa): void
    {
        Cache::forget('rekap:all:v1');
    }

    public function forceDeleted(Desa $desa): void
    {
        Cache::forget('rekap:all:v1');
    }
}
