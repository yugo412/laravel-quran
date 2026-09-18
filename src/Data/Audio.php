<?php

namespace Yugo\Quran\Data;

final readonly class Audio
{
    public function __construct(
        public string $path,
        public ?string $reciter = null,
        public ?string $style = null,
    ) {}
}
