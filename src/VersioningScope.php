<?php

namespace BinaryCocoa\Versioning;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;
use Carbon\Carbon;

/**
 * Class VersioningScope
 * @package BinaryCocoa\Versioning
 */
class VersioningScope implements Scope {

	/**
	 * All of the extensions to be added to the builder.
	 *
	 * @var array
	 */
	protected $extensions = ['Version', 'AllVersions', 'Moment'];

	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model|\BinaryCocoa\Versioning\Versionable  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model) {
		if (!$this->hasVersionJoin($builder, $model->getVersionTable())) {
			$builder->join($model->getVersionTable(), static function (JoinClause $join) use ($model) {
				$join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
				$join->on($model->getQualifiedVersionColumn(), '=', $model->getQualifiedLatestVersionColumn());
			});
		}

		$this->extend($builder);
	}

	/**
	 * Remove the scope from the given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model|\BinaryCocoa\Versioning\Versionable  $model
	 * @return void
	 */
	public function remove(Builder $builder, Model $model): void {
		$table = $model->getVersionTable();

		$query = $builder->getQuery();

		$query->joins = collect($query->joins)->reject(function ($join) use ($table) {
			return $this->isVersionJoinConstraint($join, $table);
		})->values()->all();
	}

	/**
	 * Extend the query builder with the needed functions.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	public function extend(Builder $builder): void {
		foreach ($this->extensions as $extension) {
			$this->{"add{$extension}"}($builder);
		}
	}

	/**
	 * Add the version extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addVersion(Builder $builder): void {
		$builder->macro('version', function (Builder $builder, $version) {
			/** @var Model|\BinaryCocoa\Versioning\Versionable $model */
			$model = $builder->getModel();

			$this->remove($builder, $builder->getModel());

			$builder->join($model->getVersionTable(), static function (JoinClause $join) use ($model, $version) {
				$join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
				$join->where($model->getQualifiedVersionColumn(), '=', $version);
			});

			return $builder;
		});
	}

	/**
	 * Add the allVersions extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addAllVersions(Builder $builder): void {
		$builder->macro('allVersions', function (Builder $builder) {
			/** @var Model|\BinaryCocoa\Versioning\Versionable $model */
			$model = $builder->getModel();

			$this->remove($builder, $builder->getModel());

			$builder->join($model->getVersionTable(), static function (JoinClause $join) use ($model) {
				$join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
			});

			return $builder;
		});
	}

	/**
	 * Add the moment extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addMoment(Builder $builder): void {
		$builder->macro('moment', function (Builder $builder, Carbon $moment) {
			/** @var Model|\BinaryCocoa\Versioning\Versionable $model */
			$model = $builder->getModel();

			$this->remove($builder, $builder->getModel());

			$builder->join($model->getVersionTable(), static function (JoinClause $join) use ($model, $moment) {
				$join->on($model->getQualifiedKeyName(), '=', $model->getQualifiedVersionKeyName());
				$join->where($model->getVersionTable() . '.updated_at', '<=', $moment)
					 ->orderBy($model->getVersionTable() . '.updated_at', 'desc')
					 ->limit(1);
			})
					->orderBy($model->getVersionTable() . '.updated_at', 'desc')
					->limit(1);

			return $builder;
		});
	}

	/**
	 * Determine if the given join clause is a version constraint.
	 *
	 * @param  \Illuminate\Database\Query\JoinClause   $join
	 * @param  string  $table
	 * @return bool
	 */
	protected function isVersionJoinConstraint(JoinClause $join, $table): bool {
		return $join->type === 'inner' && $join->table === $table;
	}

	/**
	 * Determine if the given builder contains a join with the given table
	 *
	 * @param Builder $builder
	 * @param string $table
	 *
	 * @return bool
	 */
	protected function hasVersionJoin(Builder $builder, string $table): bool {
		return collect($builder->getQuery()->joins)->pluck('table')->contains($table);
	}
}
