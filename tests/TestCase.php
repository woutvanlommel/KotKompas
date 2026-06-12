<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CI heeft geen Vite-build; zonder dit gooit @vite() in de layout een
        // 500 op elke publieke pagina zodra public/build/manifest.json ontbreekt.
        $this->withoutVite();
    }
}
