<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Revision\Revision;

final class InvalidRevisionDefinition extends \InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function invalidDefinition($definition): self
    {
        return new self(
            sprintf(
                'Revision definition must be a Closure or the name of a class implementing %s, got %s.',
                Revision::class,
                is_object($definition)
                    ? get_class($definition)
                    : gettype($definition)
            )
        );
    }
}