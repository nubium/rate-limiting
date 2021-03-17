<?php
namespace Nubium\RateLimiting;


interface IGrantActionException
{


	/**
	 * @return null|string
	 */
	public function getRequiredAction(string $accessStorageKey = null);


	/**
	 * Grant (temporary) rate limiting exception.
	 *
	 * @return void
	 */
	public function grantException(string $accessStorageKey = null);


	/**
	 * Returns TRUE if (temporary) rate limiting exception has been granted.
	 * @return bool
	 */
	public function hasException(string $accessStorageKey = null);


	public function hadExceptionInPast(string $accessStorageKey = null): bool;
}
