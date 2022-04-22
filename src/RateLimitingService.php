<?php
declare(strict_types=1);

namespace Nubium\RateLimiting;

use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\IRule;
use Nubium\RateLimiting\Storages\StorageException;
use Psr\Log\LoggerInterface;

class RateLimitingService implements IRateLimitingService
{
	/** @var IRule[] */
	private array$whitelistRules;
	/** @var IRule[] */
	private array $blacklistRules;
	private Storages\IAllowAccessStorage $allowAccessStorage;
	private LoggerInterface $logger;
	private int $allowAccessLength;


	/**
	 * @param IRule[] $whitelistRules
	 * @param IRule[] $blacklistRules
	 */
	public function __construct(
		array $whitelistRules,
		array $blacklistRules,
		Storages\IAllowAccessStorage $allowAccessStorage,
		LoggerInterface $logger,
		int $allowAccessLength
	) {
		$this->whitelistRules = $whitelistRules;
		$this->blacklistRules = $blacklistRules;
		$this->allowAccessStorage = $allowAccessStorage;
		$this->logger = $logger;
		$this->allowAccessLength = $allowAccessLength;
	}


	/**
	 * @inheritDoc
	 */
	public function shouldRateLimit(IRateLimitingContext $context, ?string $accessStorageKey = null, ?string $rulesKey = null): bool
	{
		$keyString = (string)$accessStorageKey;

		if ($this->allowAccessStorage->hasAccess($keyString)) {
			$this->allowAccessStorage->hit($keyString);
			return false;
		}

		if ($this->getRequiredAction($keyString) !== null) {
			// pokud potrebujeme captchu neni treba matchovat pravidla
			return true;
		}

		try {
			$actions = $this->getMatchingActions($rulesKey, $context);
		} catch (StorageException $e) {
			$this->logger->error('Rate limiting bypassed: ' . $e->getMessage(), [
				'exception' => $e,
			]);
			return false;	// pokud storage vytimeoutuje, chybu uzivateli zatajime a nelimitujeme
		}

		// ak nemame ziadne akcie ktore je potrebne splnit, tak je uzivatel ok
		if (!$actions) {
			return false;
		}

		$action = array_shift($actions);
		$this->allowAccessStorage->setRequiredAction($keyString, $action);

		return true;
	}


	public function getRequiredAction(string $accessStorageKey = null): ?string
	{
		return $this->allowAccessStorage->getRequiredAction((string)$accessStorageKey);
	}


	/**
	 * Grant (temporary) rate limiting exception.
	 */
	public function grantException(string $accessStorageKey = null): void
	{
		$this->allowAccessStorage->grantAccess((string)$accessStorageKey, $this->allowAccessLength);
	}


	public function hasException(string $accessStorageKey = null): bool
	{
		return $this->allowAccessStorage->hasAccess((string)$accessStorageKey);
	}


	public function hadExceptionInPast(string $accessStorageKey = null): bool
	{
		return $this->allowAccessStorage->hadAccessInPast((string)$accessStorageKey);
	}


	/**
	 * Vraci seznam akci z matchujicich pravidel
	 * @return string[]
	 * @throws StorageException
	 */
	protected function getMatchingActions(?string $rulesKey, IRateLimitingContext $context): array
	{
		// ak matchne whitelist pravidlo, tak mame hotovo
		foreach ($this->whitelistRules as $whitelistRule) {
			if ($whitelistRule->match($rulesKey, $context)) {
				return [];
			}
		}

		// blacklist pravidla musime prejst vsetky
		$actions = [];
		foreach ($this->blacklistRules as $blacklistRule) {
			$actions += ($blacklistRule->match($rulesKey, $context) ?: []);
		}

		return $actions;
	}
}
