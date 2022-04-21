<?php

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all IP addresses. Each IP has its own counter.
 */
class IPRule extends AbstractRateLimitingRule implements IRule
{
	const NAME = 'rl_user_ip';


	/**
	 * @inheritDoc
	 */
	public function match(?string $key, IRateLimitingContext $context): ?array
	{
		return $this->matchRule(['iprule', $context->getIp(), $key]);
	}
}
