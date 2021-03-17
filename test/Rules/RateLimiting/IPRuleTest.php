<?php

namespace Tests\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Rules\RateLimiting\IPRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class IPRuleTest extends TestCase
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

		$ipRuleTest = new IPRule([
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		], '192.168.1.1');

		$this->assertEquals($ipRuleTest->match('key'), ['foo', 'bar']);
	}


	public function testMultipleIpAddressWrite()
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
		], '192.168.1.1');

		$rule2 = new IPRule([
			'hitCount'=> 1,
			'ttl' => 300,
			'action' => ['foo', 'bar'],
			'storage' => $mock
		], '192.168.1.2');

		$this->assertEquals($rule1->match('key'), null);
		$this->assertEquals($rule2->match('key'), null);
	}

	public function testInvalidConfiguration()
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
		], '192.168.1.1');
	}
}
