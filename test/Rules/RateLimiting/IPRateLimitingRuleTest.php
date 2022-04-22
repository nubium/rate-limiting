<?php

namespace Nubium\RateLimiting\Test\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\RateLimiting\IPRateLimitingRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class IPRateLimitingRuleTest extends TestCase
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
					return strpos($key, '192.168.1.1') !== false && strpos($key, 'key') !== false;
				}),
				300
			)
			->andReturn(1)
			->getMock();

		$ipRateLimitingRule = new IPRateLimitingRule([
			'ip' => '192.168.1.1',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals($ipRateLimitingRule->match('key', $context), ['foo', 'bar']);
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
					return strpos($key, '192.168.1.1') !== false && strpos($key, 'key') !== false;
				}),
				300
			)
			->andReturn(0)
			->getMock();

		$ipRateLimitingRule = new IPRateLimitingRule([
			'ip' => '192.168.1.1',
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals($ipRateLimitingRule->match('key', $context), null);
	}

	public function testInvalidConfiguration()
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->never()
			->getMock();

		$this->expectException(\InvalidArgumentException::class);

		new IPRateLimitingRule([
			'ip' => '192.168.1.0',
			'hitCount'=> 1,
			'ttl' => 'zzz',
			'action' => 'aaa',
			'storage' => $mock
		]);
	}
}
