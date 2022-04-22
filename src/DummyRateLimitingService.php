<?php
declare(strict_types=1);

namespace Nubium\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;

/**
 * Prazdna servisa, ktora sa da pouzit aj bez zavislosti
 */
class DummyRateLimitingService implements IRateLimitingService
{
	/**
	 * @inheritDoc
	 */
	public function shouldRateLimit(IRateLimitingContext $context, ?string $accessStorageKey = null, ?string $rulesKey = null): bool
	{
		return false;
	}


	public function getRequiredAction(string $accessStorageKey = null): ?string
	{
		return null;
	}


	public function grantException(string $accessStorageKey = null): void
	{}


	public function hasException(string $accessStorageKey = null): bool
	{
		return true;
	}


	public function hadExceptionInPast(string $accessStorageKey = null): bool
	{
		return false;
	}
}
