<?php
namespace Nubium\RateLimiting\Storages;

interface IHitLogStorageFactory
{
	public function get(string $setName): IHitLogStorage;
}
