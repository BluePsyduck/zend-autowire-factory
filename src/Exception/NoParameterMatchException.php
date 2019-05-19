<?php

declare(strict_types=1);

namespace BluePsyduck\ZendAutoWireFactory\Exception;

use Throwable;

/**
 * The exception thrown when no alias of a parameters could be found in the container.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class NoParameterMatchException extends AutoWireException
{
    /**
     * The message template of the exception.
     */
    private const MESSAGE = 'Unable to auto-wire parameter %s of class %s.';

    /**
     * Initializes the exception.
     * @param string $className
     * @param string $parameterName
     * @param Throwable|null $previous
     */
    public function __construct(string $className, string $parameterName, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $parameterName, $className), 0, $previous);
    }
}
