<?php

namespace Tests\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Rules\RateLimiting\IPRangeRateLimitingRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class IPRangeRateLimitingRuleTest extends TestCase
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
					return strpos($key, '192.168.1.0/24') !== false && strpos($key, 'key') !== false;
				}),
				300
			)
			->andReturn(1)
			->getMock();

		$ipRateLimitingRule = new IPRangeRateLimitingRule([
			'range' => '192.168.1.0/24',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		], '192.168.1.1');

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
					return strpos($key, '192.168.1.0/24') !== false && strpos($key, 'key') !== false;
				}),
				300
			)
			->andReturn(0)
			->getMock();

		$ipRateLimitingRule = new IPRangeRateLimitingRule([
			'range' => '192.168.1.0/24',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		], '192.168.1.1');

		$this->assertEquals($ipRateLimitingRule->match('key'), null);
	}

	public function testInvalidConfiguration()
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->never()
			->getMock();

		$this->expectException(\InvalidArgumentException::class);

		new IPRangeRateLimitingRule([
			'range' => '192.168.1.0/24',
			'hitCount'=> 1,
			'ttl' => 'zzz',
			'action' => 'aaa',
			'storage' => $mock
		], '192.168.1.1');
	}
}
