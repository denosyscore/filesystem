<?php

declare(strict_types=1);

namespace CFXP\Core\Filesystem\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when an invalid disk is requested.
 */
class InvalidDiskException extends InvalidArgumentException
{
    public function __construct(string $disk, ?\Throwable $previous = null)
    {
        parent::__construct("Disk [{$disk}] is not configured.", 0, $previous);
    }
}
