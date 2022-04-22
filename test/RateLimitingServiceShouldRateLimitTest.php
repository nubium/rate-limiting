<?php
declare(strict_types=1);

namespace Tests\Tools\RateLimiting\RateLimitingService;

use Nubium\IpTools\GeoIPFacade;
use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\RateLimitingService;
use Nubium\RateLimiting\Rules\IRule;
use Nubium\RateLimiting\Rules\RateLimiting\GeoIPRateLimitingRule;
use Nubium\RateLimiting\Rules\RateLimiting\IPRangeRateLimitingRule;
use Nubium\RateLimiting\Rules\RateLimiting\IPRule;
use Nubium\RateLimiting\Storages\IAllowAccessStorage;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RateLimitingServiceShouldRateLimitTest extends TestCase
{

	/**
	 * @param IRule[] $whitelistRules
	 * @param IRule[] $blacklistRules
	 */
	public function prepareService(
		array $whitelistRules,
		array $blacklistRules,
		IAllowAccessStorage $allowAccessStorage,
		int $allowAccessLength
	): RateLimitingService {
		$service = new RateLimitingService(
			$whitelistRules,
			$blacklistRules,
			$allowAccessStorage,
			\Mockery::mock(LoggerInterface::class),
			$allowAccessLength
		);
		return $service;
	}


	public function testRulesDoNotMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);

		$geoIpFacade = $this->createMockGeoIPFacade();
		$mockStorage = $this->createMockStorage();
		$context = $this->createMockContext('127.0.0.1');

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareGeoIpRule('cn', 1, $mockStorage, $geoIpFacade),
			$this->prepareIPRangeRule('128.0.0.0/8', 10, $mockStorage),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 100; $i++) {
			$this->assertFalse($service->shouldRateLimit($context));
		}
	}


	public function testGeoIPRulesWillMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);
		$allowAccessStorage->shouldReceive('setRequiredAction')->with(null, 'christmas');

		$geoIpFacade = $this->createMockGeoIPFacade();
		$mockStorage = $this->createMockStorage();
		$context = $this->createMockContext('127.0.0.2');

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareGeoIpRule('de', 50, $mockStorage, $geoIpFacade),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 49; $i++) {
			$this->assertFalse($service->shouldRateLimit($context));
		}

		$this->assertTrue($service->shouldRateLimit($context));
	}


	public function testIPRangeRulesWillMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);
		$allowAccessStorage->shouldReceive('setRequiredAction')->with(null, 'christmas');

		$mockStorage = $this->createMockStorage();
		$context = $this->createMockContext('127.0.0.1');

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareIPRangeRule('127.0.0.0/8', 10, $mockStorage),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 9; $i++) {
			$this->assertFalse($service->shouldRateLimit($context));
		}

		$this->assertTrue($service->shouldRateLimit($context));
	}


	public function testIPRulesWillMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);
		$allowAccessStorage->shouldReceive('setRequiredAction')->with(null, 'christmas');

		$mockStorage = $this->createMockStorage();
		$context = $this->createMockContext('168.156.12.57');

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareIpRule(8, $mockStorage),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 7; $i++) {
			$this->assertFalse($service->shouldRateLimit($context));
		}

		$this->assertTrue($service->shouldRateLimit($context));
	}


	private function prepareGeoIpRule(
		string $country,
		int $hitCount,
		IHitLogStorage $mockStorage,
		GeoIPFacade $geoIpFacade
	): IRule {
		return new GeoIPRateLimitingRule(
			[
				'country' => $country,
				'hitCount' => $hitCount,
				'ttl' => 300,
				'action' => ['christmas'],
				'storage' => $mockStorage,
			],
			$geoIpFacade
		);
	}


	private function prepareIPRangeRule(string $range, int $hitCount, IHitLogStorage $mockStorage): IRule
	{
		return new IPRangeRateLimitingRule([
			'hitCount' => $hitCount,
			'range' => $range,
			'ttl' => 300,
			'action' => ['christmas'],
			'storage' => $mockStorage,
		]);
	}


	private function prepareIpRule(int $hitCount, IHitLogStorage $mockStorage): IRule
	{
		return new IPRule([
			'hitCount' => $hitCount,
			'ttl' => 300,
			'action' => ['christmas'],
			'storage' => $mockStorage,
		]);
	}


	/**
	 * @return \Mockery\MockInterface|IHitLogStorage
	 */
	private function createMockStorage(): IHitLogStorage
	{
		$counter = 0;
		/** @var IHitLogStorage|\Mockery\MockInterface $mockStorage */
		$mockStorage = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->andReturnUsing(function () use (&$counter) {
				return ++$counter;
			})
			->getMock();

		return $mockStorage;
	}

	/**
	 * @return \Mockery\MockInterface|GeoIPFacade
	 */
	private function createMockGeoIPFacade(): GeoIPFacade
	{
		/** @var \Mockery\MockInterface|GeoIPFacade $mock */
		$mock = \Mockery::mock(GeoIPFacade::class)
			->shouldReceive('getCountryCodeForIp')
			->andReturn('de')
			->getMock();

		return $mock;
	}

	/**
	 * @return \Mockery\MockInterface|IRateLimitingContext
	 */
	private function createMockContext(string $ip): IRateLimitingContext
	{
		/** @var \Mockery\MockInterface|IRateLimitingContext $mock */
		$mock = \Mockery::mock(IRateLimitingContext::class)
			->shouldReceive('getIp')
			->andReturn($ip)
			->getMock();

		return $mock;
	}
}
