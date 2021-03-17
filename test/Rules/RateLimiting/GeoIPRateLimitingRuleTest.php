<?php

namespace Tests\RateLimiting\Rules\RateLimiting;

use Mockery\MockInterface;
use Nubium\IpTools\GeoIP;
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

		/** @var MockInterface|GeoIP $geoIpMock */
		$geoIpMock = \Mockery::mock(GeoIP::class)
			->shouldReceive('getCountryCode')
			->once()
			->andReturn('cz')
			->getMock();

		$ipRateLimitingRule = new GeoIPRateLimitingRule([
			'country' => 'cz',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock,
		], '192.168.1.1', $geoIpMock);

		$this->assertEquals($ipRateLimitingRule->match('key'), ['foo', 'bar']);
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

		/** @var MockInterface|GeoIP $geoIpMock */
		$geoIpMock = \Mockery::mock(GeoIP::class)
			->shouldReceive('getCountryCode')
			->once()
			->andReturn('cz')
			->getMock();

		$ipRateLimitingRule = new GeoIPRateLimitingRule([
			'country' => 'cz',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock,
		], '192.168.1.1', $geoIpMock);

		$this->assertEquals($ipRateLimitingRule->match('key'), null);
	}

	public function testInvalidConfiguration()
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->never()
			->getMock();

		/** @var MockInterface|GeoIP $geoIpMock */
		$geoIpMock = \Mockery::mock(GeoIP::class)
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
		], '192.168.1.1', $geoIpMock);
	}
}
