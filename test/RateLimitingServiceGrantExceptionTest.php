<?php
declare(strict_types=1);

namespace Tests\Tools\RateLimiting\RateLimitingService;

use Nubium\RateLimiting\RateLimitingService;
use Nubium\RateLimiting\Storages\IAllowAccessStorage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RateLimitingServiceGrantExceptionTest extends TestCase
{
	public function prepareService(int $allowAccessLength, bool $shouldStore): RateLimitingService
	{
		$accessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$logger = \Mockery::mock(LoggerInterface::class);

		if ($shouldStore) {
			$accessStorage->shouldReceive('grantAccess')->with(null, $allowAccessLength);
		} else {
			$accessStorage->shouldNotReceive('grantAccess')->with(null);
		}

		$service = new RateLimitingService([], [], $accessStorage, $logger, $allowAccessLength);
		return $service;
	}


	public function testExceptionIsGranted(): void
	{
		static::assertTrue(true); // todo do not test using mockery
		$service = $this->prepareService(666, true);
		$service->grantException();
	}
}
