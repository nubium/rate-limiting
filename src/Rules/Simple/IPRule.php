<?php

namespace Nubium\RateLimiting\Rules\Simple;

use Nubium\RateLimiting\Rules\IRule;
use Nubium\RateLimiting\Rules\RuleConfigHelperTrait;

/**
 * Matches all IP addresses in configuration. Does not have counter.
 */
class IPRule extends AbstractSimpleRule  implements IRule
{
	const NAME = 'simple_ip';

	use RuleConfigHelperTrait;

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

		$this->matchIp = $this->validateAndConvertValueToArray($configuration, 'ip');
		$this->ipAddress = $ipAddress;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		if (in_array($this->ipAddress, $this->matchIp)) {
			return $this->matchRule();
		}

		return null;
	}
}
