<?php

namespace Yugo\Quran;

use Illuminate\Cache\CacheManager;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Manager;
use Yugo\Quran\Contracts\QuranCatalog;
use Yugo\Quran\Contracts\QuranProvider;
use Yugo\Quran\Data\Surah;
use Yugo\Quran\Data\SurahSummary;
use Yugo\Quran\Providers\EquranProvider;
use Yugo\Quran\Providers\UmmahApiProvider;

final class QuranManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('quran.provider', 'ummahapi');
    }

    public function createEquranDriver(): QuranProvider
    {
        $config = $this->config->get('quran.providers.equran', []);

        return new EquranProvider(
            http: $this->container->make(HttpFactory::class),
            baseUrl: (string) ($config['base_url'] ?? 'https://equran.id'),
            timeout: (int) ($config['timeout'] ?? 10),
        );
    }

    public function createUmmahapiDriver(): QuranProvider
    {
        $config = $this->config->get('quran.providers.ummahapi', []);

        return new UmmahApiProvider(
            http: $this->container->make(HttpFactory::class),
            baseUrl: (string) ($config['base_url'] ?? 'https://ummahapi.com'),
            timeout: (int) ($config['timeout'] ?? 10),
        );
    }

    public function surah(int $number): Surah
    {
        $key = sprintf('quran.v4.%s.surah.%d', $this->getDefaultDriver(), $number);
        $store = $this->config->get('quran.cache_store');
        $cache = $this->container->make(CacheManager::class)->store($store);
        /** @var QuranProvider $provider */
        $provider = $this->driver();

        return $cache->remember(
            $key,
            (int) $this->config->get('quran.cache_ttl', 604800),
            fn (): Surah => $provider->getSurah($number),
        );
    }

    /**
     * @return array{label: string, url: string}
     */
    public function attribution(): array
    {
        /** @var QuranProvider $provider */
        $provider = $this->driver();

        return $provider->getAttribution();
    }

    /**
     * @return list<SurahSummary>
     */
    public function surahs(): array
    {
        /** @var QuranProvider $provider */
        $provider = $this->driver();

        if (! $provider instanceof QuranCatalog) {
            return [];
        }

        $cache = $this->container->make(CacheManager::class)->store($this->config->get('quran.cache_store'));

        return $cache->remember(
            'quran.v1.'.$this->getDefaultDriver().'.surahs',
            (int) $this->config->get('quran.cache_ttl', 604800),
            fn (): array => $provider->getSurahs(),
        );
    }
}
