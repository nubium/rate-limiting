<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Test\Rules\SimpleRule;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\Simple\IPRule;
use PHPUnit\Framework\TestCase;

class IPRuleTest extends TestCase
{
	/**
	 * Test if hitCount match
	 */
	public function testMatch(): void
	{
		$ipRateLimitingRule = new IPRule([
			'ip' => '192.168.1.1',
			'action' => ['foo', 'bar']
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.1');

		$this->assertEquals(['foo', 'bar'], $ipRateLimitingRule->match('key', $context));
	}

	/**
	 * Test if different rules using different keys
	 */
	public function testNotMatch(): void
	{
		$ipRateLimitingRule = new IPRule([
			'ip' => '192.168.1.1',
			'action' => ['foo', 'bar']
		]);

		$context = $this->createMock(IRateLimitingContext::class);
		$context->method('getIp')->willReturn('192.168.1.2');

		$this->assertEquals([], $ipRateLimitingRule->match('key', $context));
	}

	public function testInvalidConfiguration(): void
	{

		$this->expectException(\InvalidArgumentException::class);

		new IPRule([
			'ip' => '192.168.1.0',
			'action' => 'aaa'
		]);
	}
}
