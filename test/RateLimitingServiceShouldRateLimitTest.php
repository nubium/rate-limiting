<?php
declare(strict_types=1);

namespace Tests\Tools\RateLimiting\RateLimitingService;

use Nubium\IpTools\GeoIP;
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

		$geoIp = $this->prepareMockGeoIP();
		$mockStorage = $this->prepareMockStorage();

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareGeoIpRule('cn', 1, $mockStorage, $geoIp, '127.0.0.1'),
			$this->prepareIPRangeRule('127.0.0.1', '128.0.0.0/8', 10, $mockStorage),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 100; $i++) {
			$this->assertFalse($service->shouldRateLimit());
		}
	}


	public function testGeoIPRulesWillMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);
		$allowAccessStorage->shouldReceive('setRequiredAction')->with(null, 'christmas');

		$geoIp = $this->prepareMockGeoIP();
		$mockStorage = $this->prepareMockStorage();

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareGeoIpRule('de', 50, $mockStorage, $geoIp, '127.0.0.2'),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 49; $i++) {
			$this->assertFalse($service->shouldRateLimit());
		}

		$this->assertTrue($service->shouldRateLimit());
	}


	public function testIPRangeRulesWillMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);
		$allowAccessStorage->shouldReceive('setRequiredAction')->with(null, 'christmas');

		$mockStorage = $this->prepareMockStorage();

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareIPRangeRule('127.0.0.1', '127.0.0.0/8', 10, $mockStorage),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 9; $i++) {
			$this->assertFalse($service->shouldRateLimit());
		}

		$this->assertTrue($service->shouldRateLimit());
	}


	public function testIPRulesWillMatch(): void
	{
		$allowAccessStorage = \Mockery::mock(IAllowAccessStorage::class);
		$allowAccessStorage->shouldReceive('hasAccess')->andReturn(false);
		$allowAccessStorage->shouldReceive('getRequiredAction')->andReturn(null);
		$allowAccessStorage->shouldReceive('setRequiredAction')->with(null, 'christmas');

		$mockStorage = $this->prepareMockStorage();

		$whitelistRules = [];
		$blacklistRules = [
			$this->prepareIpRule(8, '168.156.12.57', $mockStorage),
		];
		$service = $this->prepareService($whitelistRules, $blacklistRules, $allowAccessStorage, 60);
		for ($i = 1; $i <= 7; $i++) {
			$this->assertFalse($service->shouldRateLimit());
		}

		$this->assertTrue($service->shouldRateLimit());
	}


	private function prepareGeoIpRule(
		string $country,
		int $hitCount,
		IHitLogStorage $mockStorage,
		GeoIP $geoIp,
		string $ipAddress = '156.142.43.1'
	): IRule {
		return new GeoIPRateLimitingRule(
			[
				'country' => $country,
				'hitCount' => $hitCount,
				'ttl' => 300,
				'action' => ['christmas'],
				'storage' => $mockStorage,
			],
			$ipAddress,
			$geoIp
		);
	}


	private function prepareIPRangeRule(string $ipAddress, string $range, int $hitCount, IHitLogStorage $mockStorage): IRule
	{
		return new IPRangeRateLimitingRule([
			'hitCount' => $hitCount,
			'range' => $range,
			'ttl' => 300,
			'action' => ['christmas'],
			'storage' => $mockStorage,
		], $ipAddress);
	}


	private function prepareIpRule(int $hitCount, string $ipAddress, IHitLogStorage $mockStorage): IRule
	{
		return new IPRule([
			'hitCount' => $hitCount,
			'ttl' => 300,
			'action' => ['christmas'],
			'storage' => $mockStorage,
		], $ipAddress);
	}


	/**
	 * @return \Mockery\MockInterface|IHitLogStorage
	 */
	private function prepareMockStorage(): IHitLogStorage
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
	 * @return \Mockery\MockInterface|GeoIP
	 */
	private function prepareMockGeoIP(): GeoIP
	{
		/** @var \Mockery\MockInterface|GeoIP $mock */
		$mock = \Mockery::mock(GeoIP::class)
			->shouldReceive('getCountryCode')
			->andReturn('de')
			->getMock();

		return $mock;
	}
}
