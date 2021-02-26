<?php

namespace BinaryCocoa\Versioning;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletesTrait;

/**
 * Trait SoftDeletes
 *
 * @package BinaryCocoa\Versioning
 */
trait SoftDeletes {
	use SoftDeletesTrait;

	/**
	 * Get the fully qualified "deleted at" column.
	 *
	 * @return string
	 */
	public function getQualifiedDeletedAtColumn() {
		$deletedAt = $this->getDeletedAtColumn();

		if (isset($this->versioned) && in_array($deletedAt, $this->versioned)) {
			return $this->getVersionTable() . '.' . $deletedAt;
		}

		return $this->getTable() . '.' . $deletedAt;
	}
}
