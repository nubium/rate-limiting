<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;

/**
 * Matches all user agents in configuration. Each user agent has its own counter.
 */
class UserAgentRateLimitingRule extends AbstractRateLimitingRule
{
	public const NAME = 'rl_user_agent';

	/** @var mixed[] */
	private array $configuration;


	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$this->configuration = $configuration;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key, IRateLimitingContext $context): array
	{
		if (in_array($context->getUserAgent(), $this->configuration['userAgents'], true)) {
			return $this->matchRule([md5($context->getUserAgent()), $key]);
		}

		return [];
	}
}
