<?php

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all IP addresses. Each IP has its own counter.
 */
class IPRule extends AbstractRateLimitingRule implements IRule
{
	const NAME = 'rl_user_ip';


	/**
	 * @var string
	 */
	protected $ipAddress;


	public function __construct(array $configuration, string $ipAddress)
	{
		parent::__construct($configuration);
		$this->ipAddress = $ipAddress;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		return $this->matchRule(['iprule', $this->ipAddress, $key]);
	}
}
