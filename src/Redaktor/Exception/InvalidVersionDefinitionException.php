<?php

declare(strict_types=1);

namespace Redaktor\Exception;

use InvalidArgumentException;

class InvalidVersionDefinitionException extends InvalidArgumentException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}