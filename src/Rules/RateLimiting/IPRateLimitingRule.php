<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all IP addresses in configuration. Each IP has its own counter.
 */
class IPRateLimitingRule extends AbstractRateLimitingRule implements IRule
{
	public const NAME = 'rl_ip';

	/** @var string[] */
	protected array $matchIp;


	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$this->matchIp = $this->validateAndConvertValueToArray($configuration, 'ip');
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key, IRateLimitingContext $context): array
	{
		if (in_array($context->getIp(), $this->matchIp)) {
			return $this->matchRule([$context->getIp(), $key]);
		}

		return [];
	}
}
