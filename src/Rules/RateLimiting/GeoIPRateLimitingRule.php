<?php

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\IpTools\GeoIP;
use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all ip addresses belonging to specific country. Each country has its counter.
 */
class GeoIPRateLimitingRule extends AbstractRateLimitingRule implements IRule
{
	const NAME = 'rl_geoip';

	/**
	 * @var string[]
	 */
	protected $countries;

	/**
	 * @var GeoIP
	 */
	protected $geoIP;

	/**
	 * @var string
	 */
	protected $ipAddress;


	public function __construct(array $configuration, string $ipAddress, GeoIP $geoIP)
	{
		parent::__construct($configuration);

		$this->ipAddress = $ipAddress;
		$this->countries = array_map(
			fn($country) => (string)$country,
			$this->validateAndConvertValueToArray($configuration, 'country')
		);
		$this->geoIP = $geoIP;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		if (in_array($this->geoIP->getCountryCode(), $this->countries)) {
			return $this->matchRule(array_merge($this->countries, [$key]));
		}

		return null;
	}
}
