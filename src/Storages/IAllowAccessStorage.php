<?php
namespace Nubium\RateLimiting\Storages;

interface IAllowAccessStorage
{
	/**
	 * Zistenie ci ma uzivatel povoleny pristup
	 */
	public function hasAccess(string $key): bool;


	public function hadAccessInPast(string $key): bool;


	/**
	 * Zaznamenanie pristup
	 */
	public function hit(string $key): void;


	public function grantAccess(string $key, int $seconds): void;


	public function setRequiredAction(string $key, string $action): void;


	public function getRequiredAction(string $key): ?string;
}
