<?php
namespace Nubium\RateLimiting\Rules\Simple;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\IRule;
use Nubium\RateLimiting\Rules\RuleConfigHelperTrait;

/**
 * Matches all IP addresses in configuration. Does not have counter.
 */
class IPRule extends AbstractSimpleRule implements IRule
{
	public const NAME = 'simple_ip';

	use RuleConfigHelperTrait;

	/** @var array */
	protected array $matchIp;
	protected string $ipAddress;


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
			return $this->matchRule();
		}

		return [];
	}
}
