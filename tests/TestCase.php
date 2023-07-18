<?php

namespace LinkRestrictedAccess\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LinkRestrictedAccess\Tests\Fixtures\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \LinkRestrictedAccess\ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        //$app['config']->set('restricted-access.some_key', 'some_value');
    }

    protected function createUser($description = 'john', array $options = []): User
    {
        return User::create(array_merge([
            'email'    => "{$description}@user.test",
            'name'     => 'John Snor',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ], $options));
    }
}
