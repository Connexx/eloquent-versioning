<?php

namespace BinaryCocoa\Versioning\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use BinaryCocoa\Versioning\Exceptions\VersioningException;
use BinaryCocoa\Versioning\Tests\Models\Post;
use BinaryCocoa\Versioning\Tests\Models\User;
use BinaryCocoa\Versioning\Tests\TestCase;

/**
 * Class BuilderTest
 * @package BinaryCocoa\Versioning\Tests
 */
class BuilderTest extends TestCase {

	/**
	 * @test
	 */
	public function itWillRetrieveVersionedAttributes(): void {
		/** @var User $model */
		$model = User::factory()->create([]);

		$this->assertEquals([
			'username'          => $model->username,
			'email'             => $model->email,
			'city'              => $model->city,
			'latest_version'    => (string) $model->latest_version,
			'updated_at'        => $model->updated_at->toJson(),
			'created_at'        => $model->created_at->toJson(),
			'id'                => $model->id,
			'version'           => '1',
			'deleted_at'        => null,
		], User::first()->toArray());
	}

	/**
	 * @test
	 */
	public function itWillRetrieveTheLatestVersionedAttributes(): void {
		/** @var User $model */
		$model = User::factory()->create([]);

		$model->update([
			'city'  => 'Citadel'
		]);

		$first = User::first();
		$this->assertNotNull($first);

		$this->assertEquals(2, $first->latest_version);
		$this->assertEquals('Citadel', $first->city);
	}

	/**
	 * @test
	 */
	public function itWillRetrieveTheCorrectVersionsAttributes(): void {
		$now = now();

		/** @var User $model */
		$model = User::factory()->create([]);
		$city = $model->city;

		// Version 2 is tomorrow.
		$tomorrow = (clone $now)->addDay();
		Carbon::setTestNow($tomorrow);

		$model->update([
			'city'  => 'Citadel'
		]);

		// Version 3 is the day after tomorrow.
		$nextDay = (clone $tomorrow)->addDay();
		Carbon::setTestNow($nextDay);

		$model->update([
			'city'  => 'Ricklantis'
		]);

		// Reset Carbon now
		Carbon::setTestNow();

		$version1 = User::version(1)->find($model->id);
		$this->assertEquals(1, $version1->version);
		$this->assertEquals($city, $version1->city);
		$this->assertTrue($version1->updated_at->eq($model->created_at));
		$this->assertTrue($version1->updated_at->isBefore($model->updated_at));

		$version2 = User::version(2)->find($model->id);
		$this->assertEquals(2, $version2->version);
		$this->assertEquals('Citadel', $version2->city);
		$this->assertTrue($version2->updated_at->isAfter($model->created_at));
		$this->assertTrue($version2->updated_at->isBefore($model->updated_at));

		$version3 = User::version(3)->find($model->id);
		$this->assertEquals(3, $version3->version);
		$this->assertEquals('Ricklantis', $version3->city);
		$this->assertTrue($version3->updated_at->isAfter($model->created_at));
		$this->assertTrue($version3->updated_at->equalTo($model->updated_at));
	}

	/**
	 * @test
	 */
	public function itWillRetrieveAllVersions(): void {
		/** @var User $model */
		$model = User::factory()->create([]);
		$city = $model->city;

		$model->update([
			'city'  => 'Citadel'
		]);

		$model->update([
			'city'  => 'Ricklantis'
		]);

		$versions = User::allVersions()->get()->toArray();
		$this->assertCount(3, $versions);

		$expected = [
			// Version 1
			1  => $city,
			// Version 2
			2 => 'Citadel',
			// Version 3
			3 => 'Ricklantis',
		];

		foreach ($versions as $modelVersion) {
			$this->assertArrayHasKey('version', $modelVersion);
			$version = $modelVersion['version'];
			$this->assertEquals($expected[(int) $version], $modelVersion['city']);
		}
	}

	/**
	 * @test
	 */
	public function itWillRetrieveTheCorrectMomentsAttributes(): void {
		/** @var User $model */
		$model = User::factory()->create([
			'updated_at' => Carbon::now()->subDays(2)
		]);
		$date = $model->created_at;

		DB::table('users_version')->insert([
			'ref_id'        => 1,
			'version'       => 2,
			'email'         => $model->email,
			'city'          => 'Citadel',
			'updated_at'    => $date->copy()->addDay()
		]);

		DB::table('users_version')->insert([
			'ref_id'        => 1,
			'version'       => 3,
			'email'         => $model->email,
			'city'          => 'Ricklantis',
			'updated_at'    => $date->copy()->addDays(2)
		]);

		$version1 = User::moment($date)->find($model->id);
		$this->assertEquals(1, $version1->version);

		$version2 = User::moment($date->copy()->addDay())->find($model->id);
		$this->assertEquals(2, $version2->version);

		$version3 = User::moment($date->copy()->addDays(2))->find($model->id);
		$this->assertEquals(3, $version3->version);
	}

	/**
	 * @test
	 */
	public function itWillRemovePreviousJoins(): void {
		/** @var User $model */
		$model = User::factory()->create([]);
		$city = $model->city;

		$model->update([
			'city'  => 'Citadel'
		]);

		$builder = User::version(1);

		// It should have one join right now
		$this->assertEquals(1, collect($builder->getQuery()->joins)->where('table', '=', 'users_version')->count());

		$builder->version(2);

		// It should still have one join right now
		$this->assertEquals(1, collect($builder->getQuery()->joins)->where('table', '=', 'users_version')->count());
	}

	/**
	 * @test
	 */
	public function itWillThrowExceptionWhenKeysAreTooLong(): void {
		/** @var User $model */
		$model = User::factory()->create([]);

		$this->expectException(VersioningException::class);

		$builder = $model->newQuery();
		$builder->update([
						   'a.long.key'  => 'Citadel',
					   ]);
	}

	/**
	 * @test
	 *
	 * @dataProvider modelProvider
	 * @param string $model
	 */
	public function itWillDeleteTheVersionedTable(string $model): void {
		/** @var User|Post|string $model */
		$model::factory()->create([]);
		$model::factory()->create([]);

		$model::version(1)->delete();

		$this->assertEquals(0, User::all()->count());
	}

	/**
	 * @test
	 *
	 * @dataProvider modelProvider
	 * @param string $model
	 */
	public function itWillForceDeleteTheVersionedTable(string $model): void {
		/** @var User|Post|string $model */
		$model::factory()->create([]);
		$model::factory()->create([]);

		$model::version(1)->forceDelete();

		$this->assertEquals(0, User::all()->count());
	}

	/**
	 * @return array
	 */
	public function modelProvider(): array {
		return [
			[
				User::class
			],
			[
				Post::class
			]
		];
	}
}
