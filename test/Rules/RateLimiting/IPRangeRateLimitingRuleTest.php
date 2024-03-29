<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Test\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\RateLimiting\IPRangeRateLimitingRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class IPRangeRateLimitingRuleTest extends TestCase
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
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals(['foo', 'bar'], $ipRateLimitingRule->match('key', $context));
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
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals([], $ipRateLimitingRule->match('key', $context));
	}

	public function testInvalidConfiguration(): void
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
		]);
	}
}
