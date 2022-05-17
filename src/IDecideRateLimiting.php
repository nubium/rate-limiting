<?php
namespace Nubium\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;

interface IDecideRateLimiting
{
	/**
	 * Test rate limiting rules.
	 */
	public function shouldRateLimit(IRateLimitingContext $context, ?string $accessStorageKey = null, ?string $rulesKey = null): bool;
}
