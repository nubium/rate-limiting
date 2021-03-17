<?php

namespace Tests\RateLimiting\Rules\SimpleRule;

use Nubium\RateLimiting\Rules\Simple\IPRule;
use PHPUnit\Framework\TestCase;

class IPRuleTest extends TestCase
{
	/**
	 * Test if hitCount match
	 */
	public function testMatch()
	{
		$ipRateLimitingRule = new IPRule([
			'ip' => '192.168.1.1',
			'action' => ['foo', 'bar']
		], '192.168.1.1');

		$this->assertEquals($ipRateLimitingRule->match('key'), ['foo', 'bar']);
	}

	/**
	 * Test if different rules using different keys
	 */
	public function testNotMatch()
	{
		$ipRateLimitingRule = new IPRule([
			'ip' => '192.168.1.1',
			'action' => ['foo', 'bar']
		], '192.168.1.2');

		$this->assertEquals($ipRateLimitingRule->match('key'), null);
	}

	public function testInvalidConfiguration()
	{

		$this->expectException(\InvalidArgumentException::class);

		new IPRule([
			'ip' => '192.168.1.0',
			'action' => 'aaa'
		], '192.168.1.1');
	}
}
