<?php

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all IP addresses in configuration. Each IP has its own counter.
 */
class IPRateLimitingRule extends AbstractRateLimitingRule implements IRule
{
	const NAME = 'rl_ip';

	/**
	 * @var array
	 */
	protected $matchIp;

	/**
	 * @var string
	 */
	protected $ipAddress;


	public function __construct(array $configuration, string $ipAddress)
	{
		parent::__construct($configuration);

		$this->ipAddress = $ipAddress;
		$this->matchIp = $this->validateAndConvertValueToArray($configuration, 'ip');
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		if (in_array($this->ipAddress, $this->matchIp)) {
			return $this->matchRule([$this->ipAddress, $key]);
		}

		return [];
	}
}
