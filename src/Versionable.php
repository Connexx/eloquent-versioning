<?php

namespace BinaryCocoa\Versioning;

use Illuminate\Support\Arr;

/**
 * Trait Versionable
 * @package BinaryCocoa\Versioning
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method static static|\Illuminate\Database\Eloquent\Builder|Builder version($version)
 * @method static static|\Illuminate\Database\Eloquent\Builder|Builder allVersions()
 * @method static static|\Illuminate\Database\Eloquent\Builder|Builder moment(\Carbon\Carbon $moment)
 */
trait Versionable {

	/**
	 * Boot the versionable trait for a model.
	 *
	 * @return void
	 */
	public static function bootVersionable(): void {
		static::addGlobalScope(new VersioningScope());
	}

	/**
	 * Create a new model instance that is existing.
	 *
	 * @param  array  $attributes
	 * @param  string|null  $connection
	 * @return static|\Illuminate\Database\Eloquent\Model
	 */
	public function newFromBuilder($attributes = array(), $connection = null) {
		// hide ref_id from model, because ref_id == id
		$attributes = Arr::except((array) $attributes, $this->getVersionKeyName());

		return parent::newFromBuilder($attributes, $connection);
	}

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param  \Illuminate\Database\Query\Builder $query
	 * @return \BinaryCocoa\Versioning\Builder|static
	 */
	public function newEloquentBuilder($query) {
		return new Builder($query);
	}

	/**
	 * Get the names of the attributes that are versioned.
	 *
	 * @return array
	 */
	public function getVersionedAttributeNames(): array {
		return (! empty($this->versioned)) ? $this->versioned : [];
	}

	/**
	 * Get the version key name.
	 *
	 * @return string
	 */
	public function getVersionKeyName(): string {
		return 'ref_' . $this->getKeyName();
	}

	/**
	 * Get the version table associated with the model.
	 *
	 * @return string
	 */
	public function getVersionTable(): string {
		return $this->getTable() . '_version';
	}

	/**
	 * Get the table qualified version key name.
	 *
	 * @return string
	 */
	public function getQualifiedVersionKeyName(): string {
		return $this->getVersionTable() . '.' . $this->getVersionKeyName();
	}

	/**
	 * Get the name of the "latest version" column.
	 *
	 * @return string
	 */
	public function getLatestVersionColumn(): string {
		return defined('static::LATEST_VERSION') ? static::LATEST_VERSION : 'latest_version';
	}

	/**
	 * Get the fully qualified "latest version" column.
	 *
	 * @return string
	 */
	public function getQualifiedLatestVersionColumn(): string {
		return $this->getTable() . '.' . $this->getLatestVersionColumn();
	}

	/**
	 * Get the name of the "version" column.
	 *
	 * @return string
	 */
	public function getVersionColumn(): string {
		return defined('static::VERSION') ? static::VERSION : 'version';
	}

	/**
	 * Get the fully qualified "version" column.
	 *
	 * @return string
	 */
	public function getQualifiedVersionColumn(): string {
		return $this->getVersionTable() . '.' . $this->getVersionColumn();
	}
}
