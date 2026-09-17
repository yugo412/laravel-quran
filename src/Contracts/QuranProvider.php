<?php

namespace Yugo\Quran\Contracts;

use Yugo\Quran\Data\Surah;

interface QuranProvider
{
    public function getSurah(int $number): Surah;

    /**
     * @return array{label: string, url: string}
     */
    public function getAttribution(): array;
}
