<?php

namespace Tests\RateLimiting\Rules\RateLimiting;

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
	public function testHitCountMatch()
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

		$this->assertEquals($ipRateLimitingRule->match('key', $this->createMock(IRateLimitingContext::class)), ['foo', 'bar']);
	}

	/**
	 * Test return value if hitCount less than in configure
	 */
	public function testHitCountLessThanConfigure()
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

		$this->assertEquals($ipRateLimitingRule->match('key', $this->createMock(IRateLimitingContext::class)), null);
	}

	public function testInvalidConfiguration()
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
