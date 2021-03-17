<?php

namespace Nubium\RateLimiting;

use Nubium\RateLimiting\Storages\StorageException;
use Psr\Log\LoggerInterface;

class RateLimitingService implements IRateLimitingService
{
	/**
	 * @var Rules\IRule[]
	 */
	private $whitelistRules;

	/**
	 * @var Rules\IRule[]
	 */
	private $blacklistRules;

	/**
	 * @var Storages\IAllowAccessStorage
	 */
	private $allowAccessStorage;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var int Seconds
	 */
	private $allowAccessLength;


	public function __construct(
		array $whitelistRules,
		array $blacklistRules,
		Storages\IAllowAccessStorage $allowAccessStorage,
		LoggerInterface $logger,
		$allowAccessLength
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
	public function shouldRateLimit(?string $accessStorageKey = null, ?string $rulesKey = null): bool
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
			$actions = $this->getMatchingActions($rulesKey);
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


	/**
	 * @return null|string
	 */
	public function getRequiredAction(string $accessStorageKey = null)
	{
		return $this->allowAccessStorage->getRequiredAction((string)$accessStorageKey);
	}


	/**
	 * Grant (temporary) rate limiting exception.
	 */
	public function grantException(string $accessStorageKey = null)
	{
		$this->allowAccessStorage->grantAccess((string)$accessStorageKey, $this->allowAccessLength);
	}


	/**
	 * @return bool
	 */
	public function hasException(string $accessStorageKey = null)
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
	protected function getMatchingActions(?string $rulesKey): array
	{
		// ak matchne whitelist pravidlo, tak mame hotovo
		foreach ($this->whitelistRules as $whitelistRule) {
			if ($whitelistRule->match($rulesKey)) {
				return [];
			}
		}

		// blacklist pravidla musime prejst vsetky
		$actions = [];
		foreach ($this->blacklistRules as $blacklistRule) {
			$actions += ($blacklistRule->match($rulesKey) ?: []);
		}

		return $actions;
	}
}
