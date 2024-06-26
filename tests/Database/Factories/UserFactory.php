<?php

namespace BinaryCocoa\Versioning\Tests\Database\Factories;

use BinaryCocoa\Versioning\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class UserFactory
 */
class UserFactory extends Factory {
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = User::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition() {
		return [
			'email'         => $this->faker->unique()->safeEmail,
			'username'      => $this->faker->userName,
			'city'          => $this->faker->city
		];
	}
}
