<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use InvalidArgumentException;

class InvalidVersionDefinitionException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function empty(string $version): self
    {
        return new self(
            "Version definition [$version] cannot be empty."
        );
    }
}
