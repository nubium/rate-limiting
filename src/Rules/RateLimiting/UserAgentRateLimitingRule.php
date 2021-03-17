<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\RateLimiting;

/**
 * Matches all user agents in configuration. Each user agent has its own counter.
 */
class UserAgentRateLimitingRule extends AbstractRateLimitingRule
{
	const NAME = 'rl_user_agent';

	/** @var array */
	private $configuration;

	/** @var string */
	private $userAgent;


	public function __construct(array $configuration, string $userAgent)
	{
		parent::__construct($configuration);

		$this->configuration = $configuration;
		$this->userAgent = $userAgent;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		if (in_array($this->userAgent, $this->configuration['userAgents'], true)) {
			return $this->matchRule([md5($this->userAgent), $key]);
		}

		return [];
	}
}
