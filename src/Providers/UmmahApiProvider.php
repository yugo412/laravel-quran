<?php

namespace Yugo\Quran\Providers;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Yugo\Quran\Contracts\QuranCatalog;
use Yugo\Quran\Contracts\QuranProvider;
use Yugo\Quran\Data\Surah;
use Yugo\Quran\Data\SurahSummary;
use Yugo\Quran\Data\Verse;

final class UmmahApiProvider implements QuranCatalog, QuranProvider
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function getSurah(int $number): Surah
    {
        /** @var array{
         *     surah: array{
         *         number: int,
         *         name_arabic: string,
         *         name_english: string,
         *         name_translation: string,
         *         revelation_place: string,
         *         verses_count: int
         *     },
         *     verses: list<array{
         *         ayah: int,
         *         arabic: string,
         *         transliteration: string,
         *         translations: array{indonesian?: string, sahih_international?: string}
         *     }>
         * } $response
         */
        $response = $this->request('/api/quran/surah/'.$number)->json('data');

        return new Surah(
            number: (int) $response['surah']['number'],
            name: (string) $response['surah']['name_arabic'],
            latinName: (string) $response['surah']['name_english'],
            verseCount: (int) $response['surah']['verses_count'],
            meaning: (string) $response['surah']['name_translation'],
            verses: array_map(
                fn (array $verse): Verse => new Verse(
                    number: (int) $verse['ayah'],
                    arabic: (string) $verse['arabic'],
                    latin: (string) $verse['transliteration'],
                    trans: [
                        'en' => $verse['translations']['sahih_international'] ?? null,
                        'id' => $verse['translations']['indonesian'] ?? null,
                    ],
                ),
                $response['verses'],
            ),
            revelationPlace: (string) $response['surah']['revelation_place'],
        );
    }

    /**
     * @return array{label: string, url: string}
     */
    public function getAttribution(): array
    {
        return [
            'label' => 'UmmahAPI',
            'url' => 'https://ummahapi.com',
        ];
    }

    /**
     * @return list<SurahSummary>
     */
    public function getSurahs(): array
    {
        /** @var list<array{
         *     number: int,
         *     name_arabic: string,
         *     name_english: string,
         *     verses_count: int,
         *     name_translation?: string
         * }> $response
         */
        $response = $this->request('/api/quran/surahs')->json('data.surahs');

        return array_map(
            fn (array $surah): SurahSummary => new SurahSummary(
                number: (int) $surah['number'],
                name: (string) $surah['name_arabic'],
                latinName: (string) $surah['name_english'],
                verseCount: (int) $surah['verses_count'],
                meaning: isset($surah['name_translation']) ? (string) $surah['name_translation'] : null,
            ),
            $response,
        );
    }

    private function request(string $path): Response
    {
        return $this->http
            ->timeout($this->timeout)
            ->get(rtrim($this->baseUrl, '/').$path)
            ->throw();
    }
}
