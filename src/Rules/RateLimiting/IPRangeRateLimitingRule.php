<?php

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\IpTools\IpList;
use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all ip addresses belonging to subnet. Each subnet has its counter.
 */
class IPRangeRateLimitingRule extends AbstractRateLimitingRule implements IRule
{
	const NAME = 'rl_ip_range';

	/**
	 * @var IpList
	 */
	protected $matchIpList;

	/**
	 * @var string[]
	 */
	protected $matchIps;

	/**
	 * @var string
	 */
	protected $ipAddress;


	public function __construct(array $configuration, string $ipAddress)
	{
		parent::__construct($configuration);

		$this->ipAddress = $ipAddress;
		$this->matchIps = array_map(
			fn($ip) => (string)$ip,
			$this->validateAndConvertValueToArray($configuration, 'range')
		);
		$this->matchIpList = IpList::createFromArray($this->matchIps);
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		if ($this->matchIpList->contains($this->ipAddress)) {
			return $this->matchRule(array_merge($this->matchIps, [$key]));
		}

		return null;
	}
}
