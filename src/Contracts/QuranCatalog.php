<?php

namespace Yugo\Quran\Contracts;

use Yugo\Quran\Data\SurahSummary;

interface QuranCatalog
{
    /**
     * @return list<SurahSummary>
     */
    public function getSurahs(): array;
}
