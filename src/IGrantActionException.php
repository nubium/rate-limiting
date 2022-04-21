<?php
namespace Nubium\RateLimiting;

interface IGrantActionException
{


	public function getRequiredAction(string $accessStorageKey = null): ?string;


	/**
	 * Grant (temporary) rate limiting exception.
	 */
	public function grantException(string $accessStorageKey = null): void;


	/**
	 * Returns TRUE if (temporary) rate limiting exception has been granted.
	 */
	public function hasException(string $accessStorageKey = null): bool;


	public function hadExceptionInPast(string $accessStorageKey = null): bool;
}
