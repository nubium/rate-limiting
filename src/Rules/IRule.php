<?php
namespace Nubium\RateLimiting\Rules;

use Nubium\RateLimiting\Context\IRateLimitingContext;

interface IRule
{
	/**
	 * @return string[] actions if success, empty array if no match
	 */
	public function match(?string $key, IRateLimitingContext $context): array;
}
