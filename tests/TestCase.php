<?php

namespace BinaryCocoa\Versioning\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {

	/**
	 * {@inheritdoc}
	 */
	protected function getEnvironmentSetUp($app) {
		// Database
		$app['config']->set('database.default', 'testing');
		$app['config']->set('database.connections.testing', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);
	}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 *
	 * @return void
	 */
	protected function defineEnvironment($app)
	{
		$app['config']->set('database.default', 'testing');
	}

	/**
	 * Define database migrations.
	 *
	 * @return void
	 */
	protected function defineDatabaseMigrations()
	{
		$this->artisan('migrate', ['--database' => 'testing'])->run();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setUp(): void {
		parent::setUp();
		$this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
	}
}
