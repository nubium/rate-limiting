<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Context;

class RateLimitingContextFactory implements IRateLimitingContextFactory
{
	public function create(string $ip, string $userAgent): IRateLimitingContext
	{
		return new RateLimitingContext($ip, $userAgent);
	}
}
