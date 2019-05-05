<?php

declare(strict_types=1);

namespace BluePsyduck\ZendAutoWireFactory\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * The exception thrown when the auto-wire failed for a service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface AutoWireException extends Throwable, NotFoundExceptionInterface
{
}
