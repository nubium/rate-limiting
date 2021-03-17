<?php
namespace Nubium\RateLimiting;

/**
 * Composite Interface IRateLimitingService
 * @package Nubium\RateLimiting
 */
interface IRateLimitingService extends IDecideRateLimiting, IGrantActionException
{

}
