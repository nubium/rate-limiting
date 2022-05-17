<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Test\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\RateLimiting\UserAgentRateLimitingRule;
use Nubium\RateLimiting\Storages\IHitLogStorage;
use PHPUnit\Framework\TestCase;

class UserAgentRateLimitingRuleTest extends TestCase
{
	public function testMatch(): void
	{
		$mockedStorage = \Mockery::mock(IHitLogStorage::class);
		$mockedStorage->shouldReceive('increment')
			->once()
			->with(\Mockery::on(
				function ($key) {
					return strpos($key, md5('foo')) !== false && strpos($key, 'key') !== false;
				}),
				60
			)
			->andReturn(1)
			->getMock();

		$rule = new UserAgentRateLimitingRule(
			[
				'userAgents' => ['foo'],
				'action' => ['bar'],
				'ttl' => 60,
				'hitCount' => 1,
				'storage' => $mockedStorage,
			],

		);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getUserAgent')->willReturn('foo');

		static::assertEquals(['bar'], $rule->match('key', $context));
	}


	public function testNotMatch(): void
	{
		$mockedStorage = \Mockery::mock(IHitLogStorage::class);
		$mockedStorage->shouldNotReceive('increment')->getMock();

		$rule = new UserAgentRateLimitingRule(
			[
				'userAgents' => ['foo'],
				'action' => ['bar'],
				'ttl' => 60,
				'hitCount' => 1,
				'storage' => $mockedStorage,
			]
		);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getUserAgent')->willReturn('baz');

		static::assertEquals([], $rule->match('key', $context));
	}
}
