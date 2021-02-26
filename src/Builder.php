<?php

namespace BinaryCocoa\Versioning;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;

/**
 * Class Builder
 *
 * @package BinaryCocoa\Versioning
 */
class Builder extends BaseBuilder {
	use BuilderTrait;
}
