<?php

namespace Yugo\Quran\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Yugo\Quran\QuranServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QuranServiceProvider::class,
        ];
    }
}
