<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\Simple;

use Nubium\RateLimiting\Rules\IRule;
use Nubium\RateLimiting\Rules\RuleConfigHelperTrait;

/**
 * Parent rule.
 */
abstract class AbstractSimpleRule implements IRule
{
	use RuleConfigHelperTrait;

	/** @var string[] */
	protected array $responseActions;


	/**
	 * @param mixed[] $configuration
	 */
	public function __construct(array $configuration)
	{
		$this->validateConfiguration(
			$configuration,
			'action',
			function ($value) {
				return is_array($value);
			}
		);

		$this->responseActions = array_map(fn($action) => (string)$action, $configuration['action']);
	}


	/**
	 * @return string[] list of rules
	 */
	protected function matchRule(): array
	{
		return $this->responseActions;
	}
}
