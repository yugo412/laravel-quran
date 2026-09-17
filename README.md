# Quran Manager for Laravel

Quran Manager for Laravel provides a configurable Quran data manager for Laravel applications.

The package uses Laravel's manager pattern, allowing the Quran data provider to be changed without changing application code.

## Installation

Install the package with Composer:

```bash
composer require yugo/laravel-quran
```

Publish the configuration file when you need to customize the defaults:

```bash
php artisan vendor:publish --tag=quran-config
```

## Configuration

```env
QURAN_PROVIDER=ummahapi
QURAN_CACHE_STORE=
QURAN_CACHE_TTL=604800
EQURAN_BASE_URL=https://equran.id
EQURAN_TIMEOUT=10
UMMAH_API_BASE_URL=https://ummahapi.com
UMMAH_API_TIMEOUT=10
```

## Usage

```php
use Yugo\Quran\Facades\Quran;

$surah = Quran::surah(1);
$verse = $surah->verse(10);
$attribution = Quran::attribution();
```

The returned `Surah` object contains the surah metadata and a list of `Verse` objects. Use `verse(int $number)` to select an individual verse without making another provider request. It returns `null` when the verse does not exist.

## Providers

### UmmahAPI

Data provided by [UmmahAPI](https://ummahapi.com).

### eQuran.id

Data provided by [eQuran.id](https://equran.id).

## Custom providers

Custom providers implement `QuranProvider` and can be registered through the manager:

```php
use Yugo\Quran\Contracts\QuranProvider;
use Yugo\Quran\Facades\Quran;

Quran::extend('custom', fn (): QuranProvider => new CustomQuranProvider);
```

A provider must implement:

```php
public function getSurah(int $number): Surah;

/**
 * @return array{label: string, url: string}
 */
public function getAttribution(): array;
```
