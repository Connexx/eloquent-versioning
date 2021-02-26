<?php

namespace BinaryCocoa\Versioning\Tests\Database\Factories;

use BinaryCocoa\Versioning\Tests\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class PostFactory
 */
class PostFactory extends Factory {
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Post::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'title' => $this->faker->title,
			'content' => $this->faker->text,
		];
	}
}
