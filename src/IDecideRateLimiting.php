<?php
namespace Nubium\RateLimiting;


interface IDecideRateLimiting
{
	/**
	 * Test rate limiting rules.
	 */
	public function shouldRateLimit(?string $accessStorageKey = null, ?string $rulesKey = null): bool;
}
