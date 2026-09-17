<?php

use Illuminate\Support\Facades\Http;
use Yugo\Quran\Contracts\QuranProvider;
use Yugo\Quran\Data\Surah;
use Yugo\Quran\Data\Verse;
use Yugo\Quran\Facades\Quran;

beforeEach(function (): void {
    config()->set('quran.provider', 'equran');
});

it('loads and normalizes a surah from the configured provider', function (): void {
    config()->set('quran.cache_store', 'array');

    Http::fake([
        'https://equran.id/api/v2/surat/1' => Http::response([
            'data' => [
                'nomor' => 1,
                'nama' => 'الفاتحة',
                'namaLatin' => 'Al-Fatihah',
                'jumlahAyat' => 1,
                'arti' => 'Pembukaan',
                'ayat' => [[
                    'nomorAyat' => 1,
                    'teksArab' => 'بِسْمِ اللّٰهِ',
                    'teksLatin' => 'Bismillaah',
                    'teksIndonesia' => 'Dengan nama Allah',
                    'audio' => [
                        '01' => 'https://example.test/audio.mp3',
                    ],
                ]],
            ],
        ]),
    ]);

    $surah = Quran::surah(1);

    expect($surah)->toBeInstanceOf(Surah::class);
    expect($surah->number)->toBe(1);
    expect($surah->latinName)->toBe('Al-Fatihah');
    expect($surah->verses[0]->trans)->toBe([
        'en' => null,
        'id' => 'Dengan nama Allah',
    ]);
});

it('selects a verse from a loaded surah', function (): void {
    $surah = new Surah(
        number: 1,
        name: 'الفاتحة',
        latinName: 'Al-Fatihah',
        verseCount: 1,
        meaning: 'Pembukaan',
        verses: [
            new Verse(
                number: 1,
                arabic: 'بِسْمِ اللّٰهِ',
                latin: 'Bismillaah',
                trans: ['id' => 'Dengan nama Allah'],
            ),
        ],
    );

    expect($surah->verse(1))->toBe($surah->verses[0])
        ->and($surah->verse(10))->toBeNull();
});

it('caches a surah using the configured cache store', function (): void {
    config()->set('quran.cache_store', 'array');

    Http::fake([
        'https://equran.id/api/v2/surat/1' => Http::response([
            'data' => [
                'nomor' => 1,
                'nama' => 'الفاتحة',
                'namaLatin' => 'Al-Fatihah',
                'jumlahAyat' => 0,
                'arti' => 'Pembukaan',
                'ayat' => [],
            ],
        ]),
    ]);

    Quran::surah(1);
    Quran::surah(1);

    Http::assertSentCount(1);
});

it('loads a cached list of surah summaries', function (): void {
    config()->set('quran.cache_store', 'array');

    Http::fake([
        'https://equran.id/api/v2/surat' => Http::response([
            'data' => [[
                'nomor' => 1,
                'nama' => 'الفاتحة',
                'namaLatin' => 'Al-Fatihah',
                'jumlahAyat' => 7,
            ]],
        ]),
    ]);

    $surahs = Quran::surahs();

    expect($surahs)->toHaveCount(1)
        ->and($surahs[0]->latinName)->toBe('Al-Fatihah');
});

it('loads a surah and its catalog from UmmahAPI', function (): void {
    config()->set('quran.provider', 'ummahapi');
    config()->set('quran.cache_store', 'array');

    Http::fake([
        'https://ummahapi.com/api/quran/surah/1' => Http::response([
            'data' => [
                'surah' => [
                    'number' => 1,
                    'name_arabic' => 'الفاتحة',
                    'name_english' => 'Al-Fatihah',
                    'name_translation' => 'The Opener',
                    'revelation_place' => 'makkah',
                    'verses_count' => 1,
                ],
                'verses' => [[
                    'ayah' => 1,
                    'arabic' => 'بِسْمِ اللّٰهِ',
                    'transliteration' => 'Bismillaah',
                    'translations' => [
                        'sahih_international' => 'In the name of Allah',
                        'indonesian' => 'Dengan nama Allah',
                    ],
                ]],
            ],
        ]),
        'https://ummahapi.com/api/quran/surahs' => Http::response([
            'data' => [
                'surahs' => [[
                    'number' => 1,
                    'name_arabic' => 'الفاتحة',
                    'name_english' => 'Al-Fatihah',
                    'verses_count' => 7,
                ]],
            ],
        ]),
    ]);

    $surah = Quran::surah(1);
    $surahs = Quran::surahs();

    expect($surah->meaning)->toBe('The Opener')
        ->and($surah->verses[0]->trans)->toBe([
            'en' => 'In the name of Allah',
            'id' => 'Dengan nama Allah',
        ])
        ->and($surahs[0]->name)->toBe('الفاتحة')
        ->and(Quran::attribution())->toBe([
            'label' => 'UmmahAPI',
            'url' => 'https://ummahapi.com',
        ]);
});

it('can resolve a custom provider through the manager', function (): void {
    config()->set('quran.provider', 'custom');

    Quran::extend('custom', fn (): QuranProvider => new class implements QuranProvider
    {
        public function getSurah(int $number): Surah
        {
            return new Surah($number, 'name', 'Custom', 0, 'meaning', []);
        }

        public function getAttribution(): array
        {
            return ['label' => 'Custom', 'url' => 'https://example.test'];
        }
    });

    expect(Quran::surah(7)->latinName)->toBe('Custom');
});
