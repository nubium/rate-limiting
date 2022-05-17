<?php
declare(strict_types=1);

namespace Nubium\RateLimiting\Rules\Simple;

use Nubium\IpTools\IpList;
use Nubium\RateLimiting\Context\IRateLimitingContext;

class IPRangeRule extends AbstractSimpleRule
{
	public const NAME = 'simple_ip_range';

	/** @var string[] */
	protected array $matchIp;
	protected IpList $matchIpList;


	/**
	 * @inheritDoc
	 */
	public function __construct(array $configuration)
	{
		parent::__construct($configuration);

		$range = $this->validateAndConvertValueToArray($configuration, 'range');
		$this->matchIpList = IpList::createFromArray($range);
		$this->matchIp = $range;
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key, IRateLimitingContext $context): array
	{
		if ($this->matchIpList->contains($context->getIp())) {
			return $this->matchRule();
		}

		return [];
	}
}
