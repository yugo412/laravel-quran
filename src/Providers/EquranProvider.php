<?php

namespace Yugo\Quran\Providers;

use Illuminate\Http\Client\Factory as HttpFactory;
use Yugo\Quran\Contracts\QuranCatalog;
use Yugo\Quran\Contracts\QuranProvider;
use Yugo\Quran\Data\Surah;
use Yugo\Quran\Data\SurahSummary;
use Yugo\Quran\Data\Verse;

final class EquranProvider implements QuranCatalog, QuranProvider
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function getSurah(int $number): Surah
    {
        $response = $this->http
            ->timeout($this->timeout)
            ->get(rtrim($this->baseUrl, '/').'/api/v2/surat/'.$number)
            ->throw()
            ->json('data');

        return new Surah(
            number: (int) $response['nomor'],
            name: (string) $response['nama'],
            latinName: (string) $response['namaLatin'],
            verseCount: (int) $response['jumlahAyat'],
            meaning: (string) $response['arti'],
            verses: array_map(
                function (array $verse): Verse {
                    return new Verse(
                        number: (int) $verse['nomorAyat'],
                        arabic: (string) $verse['teksArab'],
                        latin: (string) $verse['teksLatin'],
                        trans: [
                            'en' => null,
                            'id' => (string) $verse['teksIndonesia'],
                        ],
                    );
                },
                $response['ayat'],
            ),
            revelationPlace: $response['tempatTurun'] ?? null,
        );
    }

    /**
     * @return array{label: string, url: string}
     */
    public function getAttribution(): array
    {
        return [
            'label' => 'eQuran.id',
            'url' => 'https://equran.id',
        ];
    }

    /**
     * @return list<SurahSummary>
     */
    public function getSurahs(): array
    {
        $response = $this->http
            ->timeout($this->timeout)
            ->get(rtrim($this->baseUrl, '/').'/api/v2/surat')
            ->throw()
            ->json('data');

        return array_map(
            fn (array $surah): SurahSummary => new SurahSummary(
                number: (int) $surah['nomor'],
                name: (string) $surah['nama'],
                latinName: (string) $surah['namaLatin'],
                verseCount: (int) $surah['jumlahAyat'],
            ),
            $response,
        );
    }
}
