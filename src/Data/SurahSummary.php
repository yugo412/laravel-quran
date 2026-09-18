<?php

namespace Yugo\Quran\Data;

final readonly class SurahSummary
{
    public function __construct(
        public int $number,
        public string $name,
        public string $latinName,
        public int $verseCount,
        public ?string $meaning = null,
    ) {}
}
