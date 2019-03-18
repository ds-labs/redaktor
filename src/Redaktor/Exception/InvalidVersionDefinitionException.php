<?php

namespace Redaktor\Exception;

use InvalidArgumentException;

class InvalidVersionDefinitionException extends InvalidArgumentException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}