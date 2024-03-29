<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\RateLimiting\Rules\IRule;
use Nubium\RateLimiting\Rules\RuleConfigHelperTrait;
use Nubium\RateLimiting\Storages\IHitLogStorage;

/**
 * Parent rule.
 * TTL of all rule hits is renewed on each hit.
 */
abstract class AbstractRateLimitingRule implements IRule
{
	use RuleConfigHelperTrait;

	/** @var string[] */
	protected array $responseAction;
	protected int $hitCount;
	protected int $ttl;
	protected IHitLogStorage $hitLogStorage;


	/**
	 * @param mixed[] $configuration
	 */
	public function __construct(array $configuration)
	{
		$this->validateConfiguration(
			$configuration,
			'hitCount',
			function ($value) {
				return is_numeric($value);
			}
		);
		$this->validateConfiguration(
			$configuration,
			'ttl',
			function ($value) {
				return is_numeric($value);
			}
		);
		$this->validateConfiguration(
			$configuration,
			'action',
			function ($value) {
				return is_array($value);
			}
		);
		$this->validateConfiguration(
			$configuration,
			'storage',
			function ($value) {
				return $value instanceof IHitLogStorage;
			}
		);

		$this->ttl = (int)$configuration['ttl'];
		$this->responseAction = array_map(fn ($action) => (string)$action, $configuration['action']);
		$this->hitCount = (int)$configuration['hitCount'];
		$this->hitLogStorage = $configuration['storage'];
	}


	/**
	 * @param array<int, string|null> $keyParts
	 * @return string[] list of actions
	 */
	protected function matchRule(array $keyParts): array
	{
		$key = implode('_', $keyParts);
		$key = str_replace('\\', '_', strtolower(get_class($this)) . '_' . $key);

		$actualValue = $this->hitLogStorage->increment($key, $this->ttl);
		if ($actualValue >= $this->hitCount) {
			return $this->responseAction;
		}

		return [];
	}
}
