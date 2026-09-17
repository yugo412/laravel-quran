<?php

namespace Yugo\Quran\Facades;

use Illuminate\Support\Facades\Facade;
use Yugo\Quran\QuranManager;

final class Quran extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QuranManager::class;
    }
}
