<?php

namespace Nubium\RateLimiting\Rules;

interface IRule
{
	/**
	 * @return string[]|null actions if success, empty array if no match
	 */
	public function match(?string $key): ?array;
}
