<?php
namespace Nubium\RateLimiting\Context;

interface IRateLimitingContextFactory
{
	public function create(string $ip, string $userAgent): IRateLimitingContext;
}
