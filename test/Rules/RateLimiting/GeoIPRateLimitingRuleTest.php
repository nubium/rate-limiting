<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Test\Rules\RateLimiting;

use Mockery\MockInterface;
use Nubium\IpTools\GeoIPFacade;
use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\RateLimiting\GeoIPRateLimitingRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class GeoIPRateLimitingRuleTest extends TestCase
{
	/**
	 * Test if hitCount match
	 */
	public function testHitCountMatch(): void
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->once()
			->with(\Mockery::on(
				function ($key) {
					$this->assertStringContainsString('cz', $key);
					$this->assertStringContainsString('key', $key);
					return true;
				}),
				300
			)
			->andReturn(1)
			->getMock();

		/** @var MockInterface|GeoIPFacade $geoIpFacadeMock */
		$geoIpFacadeMock = \Mockery::mock(GeoIPFacade::class)
			->shouldReceive('getCountryCodeForIp')
			->once()
			->andReturn('cz')
			->getMock();

		$ipRateLimitingRule = new GeoIPRateLimitingRule([
			'country' => 'cz',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock,
		], $geoIpFacadeMock);

		$this->assertEquals(['foo', 'bar'], $ipRateLimitingRule->match('key', $this->createMock(IRateLimitingContext::class)));
	}

	/**
	 * Test return value if hitCount less than in configure
	 */
	public function testHitCountLessThanConfigure(): void
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->once()
			->with(\Mockery::on(
				function ($key) {
					$this->assertStringContainsString('cz', $key);
					$this->assertStringContainsString('key', $key);
					return true;
				}),
				300
			)
			->andReturn(0)
			->getMock();

		/** @var MockInterface|GeoIPFacade $geoIpFacadeMock */
		$geoIpFacadeMock = \Mockery::mock(GeoIPFacade::class)
			->shouldReceive('getCountryCodeForIp')
			->once()
			->andReturn('cz')
			->getMock();

		$ipRateLimitingRule = new GeoIPRateLimitingRule([
			'country' => 'cz',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock,
		], $geoIpFacadeMock);

		$this->assertEquals([], $ipRateLimitingRule->match('key', $this->createMock(IRateLimitingContext::class)));
	}

	public function testInvalidConfiguration(): void
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->never()
			->getMock();

		/** @var MockInterface|GeoIPFacade $geoIpFacadeMock */
		$geoIpFacadeMock = \Mockery::mock(GeoIPFacade::class)
			->shouldReceive('getCountryCode')
			->never()
			->getMock();

		$this->expectException(\InvalidArgumentException::class);

		new GeoIPRateLimitingRule([
			'county' => 'cz',
			'hitCount'=> 1,
			'ttl' => 'zzz',
			'action' => 'aaa',
			'storage' => $mock,
		], $geoIpFacadeMock);
	}
}
