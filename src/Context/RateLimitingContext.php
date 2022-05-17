<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Context;

class RateLimitingContext implements IRateLimitingContext
{
	private string $ip, $userAgent;


	public function __construct(string $ip, string $userAgent)
	{
		$this->ip = $ip;
		$this->userAgent = $userAgent;
	}


	public function getIp(): string
	{
		return $this->ip;
	}

	public function getUserAgent(): string
	{
		return $this->userAgent;
	}
}
