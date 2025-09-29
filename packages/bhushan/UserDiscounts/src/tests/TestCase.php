<?php

namespace Vendor\UserDiscounts\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Vendor\UserDiscounts\DiscountsServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [DiscountsServiceProvider::class];
    }
    protected function getEnvironmentSetUp($app)
    {
        // use sqlite in-memory for tests
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        // publish and run package migrations programmatically
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        // app user model migration for tests
        $app['db']->connection()->getSchemaBuilder()->create(
            'users',
            function ($table) {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            }
        );
    }
}
