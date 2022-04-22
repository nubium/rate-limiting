<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\RateLimiting;

use Nubium\IpTools\GeoIPFacade;
use Nubium\RateLimiting\Context\IRateLimitingContext;
use Nubium\RateLimiting\Rules\IRule;

/**
 * Matches all ip addresses belonging to specific country. Each country has its counter.
 */
class GeoIPRateLimitingRule extends AbstractRateLimitingRule implements IRule
{
	public const NAME = 'rl_geoip';

	/** @var string[] */
	protected array $countries;
	protected GeoIPFacade $geoIPFacade;


	public function __construct(array $configuration, GeoIPFacade $geoIPFacade)
	{
		parent::__construct($configuration);

		$this->countries = array_map(
			fn($country) => (string)$country,
			$this->validateAndConvertValueToArray($configuration, 'country')
		);
		$this->geoIPFacade = $geoIPFacade;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key, IRateLimitingContext $context): array
	{
		if (in_array($this->geoIPFacade->getCountryCodeForIp($context->getIp()), $this->countries)) {
			return $this->matchRule(array_merge($this->countries, [$key]));
		}

		return [];
	}
}
