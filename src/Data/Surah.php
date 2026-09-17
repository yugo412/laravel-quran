<?php

namespace Yugo\Quran\Data;

final readonly class Surah
{
    /**
     * @param  list<Verse>  $verses
     */
    public function __construct(
        public int $number,
        public string $name,
        public string $latinName,
        public int $verseCount,
        public string $meaning,
        public array $verses,
        public ?string $revelationPlace = null,
    ) {}

    public function verse(int $number): ?Verse
    {
        foreach ($this->verses as $verse) {
            if ($verse->number === $number) {
                return $verse;
            }
        }

        return null;
    }
}
