<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Test\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\RateLimiting\IPRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class IPRuleTest extends TestCase
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
					return strpos($key, '192.168.1.1') !== false && strpos($key, 'key') !== false;
				}),
				300
			)
			->andReturn(1)
			->getMock();

		$ipRuleTest = new IPRule([
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals(['foo', 'bar'], $ipRuleTest->match('key', $context));
	}


	public function testMultipleIpAddressWrite(): void
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
			->getMock()
			->shouldReceive('increment')
			->once()
			->with(\Mockery::on(
				function ($key) {
					return strpos($key, '192.168.1.2') !== false;
				}),
				300
			)
			->andReturn(0)
			->getMock();

		$rule1 = new IPRule([
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		]);

		$rule2 = new IPRule([
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals([], $rule1->match('key', $context));
		$this->assertEquals([], $rule2->match('key', $context));
	}

	public function testInvalidConfiguration(): void
	{
		$mock = \Mockery::mock(IHitLogStorage::class)
			->shouldReceive('increment')
			->never()
			->getMock();

		$this->expectException(\InvalidArgumentException::class);

		new IPRule([
			'hitCount'=> 1,
			'ttl' => 'zzz',
			'action' => 'aaa',
			'storage' => $mock
		]);
	}
}
