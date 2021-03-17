<?php
namespace Nubium\RateLimiting\Rules\Simple;


use Nubium\IpTools\IpList;

class IPRangeRule extends AbstractSimpleRule
{
	const NAME = 'simple_ip_range';

	/**
	 * @var IpList
	 */
	protected $matchIpList;

	/**
	 * @var string
	 */
	protected $matchIp;

	/**
	 * @var string
	 */
	protected $ipAddress;


	public function __construct(array $configuration, string $ipAddress)
	{
		parent::__construct($configuration);

		$this->ipAddress = $ipAddress;
		$this->matchIpList = IpList::createFromArray($this->validateAndConvertValueToArray($configuration, 'range'));
		$this->matchIp = $configuration['range'];
	}


	/**
	 * @inheritDoc
	 */
	public function match(?string $key): ?array
	{
		if ($this->matchIpList->contains($this->ipAddress)) {
			return $this->matchRule();
		}

		return null;
	}
}
