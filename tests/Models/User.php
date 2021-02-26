<?php

namespace BinaryCocoa\Versioning\Tests\Models;

use BinaryCocoa\Versioning\Tests\Database\Factories\UserFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use BinaryCocoa\Versioning\Versionable;
use BinaryCocoa\Versioning\SoftDeletes;

/**
 * Class User
 *
 * @package BinaryCocoa\Versioning\Tests\Models
 *
 * @property int $id
 * @property string $city
 * @property string $email
 * @property string $latest_version
 * @property string $password
 * @property string $remember_token
 * @property string $username
 * @property int    $version
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class User extends Authenticatable {
	use Versionable;
	use SoftDeletes;
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'email', 'username', 'city', 'latest_version',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	public $timestamps = true;

	public $versioned = ['email', 'city', 'updated_at', 'deleted_at'];

	/**
	 * Prepare a date for array / JSON serialization.
	 *
	 * @param  \DateTimeInterface  $date
	 * @return string
	 */
	protected function serializeDate(DateTimeInterface $date)
	{
	    return $date->toJson();
	}

	/**
	 * Create a new factory instance for the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Factories\Factory
	 */
	protected static function newFactory()
	{
		return UserFactory::new();
	}
}
