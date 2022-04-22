<?php

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\IpTools\IpList;
use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all ip addresses belonging to subnet. Each subnet has its counter.
 */
class IPRangeRateLimitingRule extends AbstractRateLimitingRule implements IRule
{
	public const NAME = 'rl_ip_range';

	protected IpList $matchIpList;
	/** @var string[] */
	protected array $matchIps;


	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$this->matchIps = array_map(
			fn($ip) => (string)$ip,
			$this->validateAndConvertValueToArray($configuration, 'range')
		);
		$this->matchIpList = IpList::createFromArray($this->matchIps);
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key, IRateLimitingContext $context): array
	{
		if ($this->matchIpList->contains($context->getIp())) {
			return $this->matchRule(array_merge($this->matchIps, [$key]));
		}

		return [];
	}
}
