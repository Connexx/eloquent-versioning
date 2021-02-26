<?php

namespace BinaryCocoa\Versioning\Tests\Models;

use BinaryCocoa\Versioning\Tests\Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BinaryCocoa\Versioning\Versionable;

/**
 * Class Comment
 *
 * A model without versioned timestamps.
 *
 * @package BinaryCocoa\Versioning\Tests\Models
 *
 * @property int $id
 * @property string $latest_version
 * @property string $content
 * @property string $title
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Comment extends Model {
	use Versionable;
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'content', 'latest_version', 'title',
	];

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = true;

	public $versioned = ['content'];

	/**
	 * Create a new factory instance for the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Factories\Factory
	 */
	protected static function newFactory()
	{
		return CommentFactory::new();
	}
}
