<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private static bool $migrationsRun = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->usesRefreshDatabase()) {
            $this->runMigrations();
        }
    }

    private function usesRefreshDatabase(): bool
    {
        return in_array(RefreshDatabase::class, class_uses_recursive(static::class), true);
    }

    private function runMigrations(): void
    {
        if (self::$migrationsRun) {
            return;
        }

        $this->artisan('migrate');
        self::$migrationsRun = true;

        $this->beforeApplicationDestroyed(function (): void {
            $this->artisan('migrate:reset');
            self::$migrationsRun = false;
        });
    }
}
