<?php
namespace Nubium\RateLimiting\Context;

interface IRateLimitingContext
{
	public function getIp(): string;
	public function getUserAgent(): string;
}
