<?php

namespace BinaryCocoa\Versioning\Tests\Database\Factories;

use BinaryCocoa\Versioning\Tests\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class CommentFactory
 */
class CommentFactory extends Factory {
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Comment::class;

	/**
	 * Define the model's default state.
	 *
	 * @return array
	 */
	public function definition()
	{
		return [
			'title' => $this->faker->sentence,
			'content' => $this->faker->text,
		];
	}
}
