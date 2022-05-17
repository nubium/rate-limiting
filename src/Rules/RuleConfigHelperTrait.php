<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules;

trait RuleConfigHelperTrait
{
	/**
	 * Validates element in array and convert value to array if necessarily
	 *
	 * @param mixed[] $sourceArray
	 *
	 * @return mixed[]
	 */
	protected function validateAndConvertValueToArray(array $sourceArray, string $key): array
	{
		$this->validateConfiguration($sourceArray, $key, function($value) {
			return is_string($value) || is_array($value);
		});

		$list = [];

		if (is_string($sourceArray[$key])) {
			$list = [$sourceArray[$key]];
		} else if (is_array($sourceArray[$key])) {
			$list = $sourceArray[$key];
		}

		return $list;
	}

	/**
	 * Validates element in array.
	 *
	 * @param mixed[] $sourceArray
	 */
	protected function validateConfiguration(array $sourceArray, string $key, callable $callback = null): void
	{
		if (empty($sourceArray[$key]) || ($callback !== null && !$callback($sourceArray[$key]))) {
			throw new \InvalidArgumentException(__CLASS__ . ': invalid configuration for key ' . $key);
		}
	}
}
