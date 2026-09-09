<?php

namespace App\Observers;

use App\Models\Calon;
use Illuminate\Support\Facades\Cache;

class CalonObserver
{
    public function saved(Calon $calon): void
    {
        Cache::forget('rekap:all:v1');
    }

    public function deleted(Calon $calon): void
    {
        Cache::forget('rekap:all:v1');
    }

    public function restored(Calon $calon): void
    {
        Cache::forget('rekap:all:v1');
    }

    public function forceDeleted(Calon $calon): void
    {
        Cache::forget('rekap:all:v1');
    }
}
