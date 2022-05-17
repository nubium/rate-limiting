<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Test\Context;

use Nubium\RateLimiting\Context\RateLimitingContextFactory;
use PHPUnit\Framework\TestCase;

class RateLimitingContextFactoryTest extends TestCase
{
	public function testConstruction(): void
	{
		$factory = new RateLimitingContextFactory();
		$context = $factory->create('ip', 'user-agent');

		$this->assertEquals('ip', $context->getIp());
		$this->assertEquals('user-agent', $context->getUserAgent());
	}
}
