<?php
namespace Nubium\RateLimiting;

/**
 * Prazdna servisa, ktora sa da pouzit aj bez zavislosti
 */
class DummyRateLimitingService implements IRateLimitingService
{

	/**
	 * @inheritDoc
	 */
	public function shouldRateLimit(?string $accessStorageKey = null, ?string $rulesKey = null): bool
	{
		return false;
	}


	/**
	 * @return null|string
	 */
	public function getRequiredAction(string $accessStorageKey = null)
	{
		return null;
	}


	/**
	 * @return void
	 */
	public function grantException(string $accessStorageKey = null)
	{
		return;
	}


	/**
	 * @return bool
	 */
	public function hasException(string $accessStorageKey = null)
	{
		return true;
	}


	public function hadExceptionInPast(string $accessStorageKey = null): bool
	{
		return false;
	}
}
