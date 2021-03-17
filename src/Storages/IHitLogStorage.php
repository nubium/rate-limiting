<?php

namespace Nubium\RateLimiting\Storages;

interface IHitLogStorage
{
	public function increment(string $key, int $ttl): int;
}
