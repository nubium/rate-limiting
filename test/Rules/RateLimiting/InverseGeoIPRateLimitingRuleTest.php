<?php

namespace Tests\RateLimiting\Rules\RateLimiting;

use Mockery\MockInterface;
use Nubium\IpTools\GeoIP;
use Nubium\RateLimiting\Rules\RateLimiting\InverseGeoIPRateLimitingRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class InverseGeoIPRateLimitingRuleTest extends TestCase
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
					$this->assertStringContainsString('sk', $key);
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

		$ipRateLimitingRule = new InverseGeoIPRateLimitingRule([
			'country' => 'sk',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock,
		], '192.168.1.1', $geoIpMock);

		$this->assertEquals($ipRateLimitingRule->match('key'), ['foo', 'bar']);
	}

	/**
	 * Test if hitCount match
	 */
	public function testHitCountNotMatch()
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->once()
			->with(\Mockery::on(
				function ($key) {
					$this->assertStringContainsString('sk', $key);
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
			->andReturn('sk')
			->getMock();

		$ipRateLimitingRule = new InverseGeoIPRateLimitingRule([
			'country' => 'sk',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock,
		], '192.168.1.1', $geoIpMock);

		$this->assertEquals($ipRateLimitingRule->match('key'), null);
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
					$this->assertStringContainsString('sk', $key);
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

		$ipRateLimitingRule = new InverseGeoIPRateLimitingRule([
			'country' => 'sk',
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

		new InverseGeoIPRateLimitingRule([
			'county' => 'cz',
			'hitCount'=> 1,
			'ttl' => 'zzz',
			'action' => 'aaa',
			'storage' => $mock,
		], '192.168.1.1', $geoIpMock);
	}
}
