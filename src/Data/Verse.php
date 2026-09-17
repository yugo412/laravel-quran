<?php

namespace Yugo\Quran\Data;

final readonly class Verse
{
    /**
     * @param  array<string, string|null>  $trans
     */
    public function __construct(
        public int $number,
        public string $arabic,
        public string $latin,
        public array $trans,
    ) {}
}
