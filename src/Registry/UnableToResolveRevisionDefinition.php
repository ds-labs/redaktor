<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Revision\Revision;

final class UnableToResolveRevisionDefinition extends \RuntimeException
{
    public function __construct($resolvedValue)
    {
        parent::__construct(
            sprintf(
                'Revision definition factory must return an instance or the name of a class implementing %s, returned %s.',
                Revision::class,
                is_object($resolvedValue)
                    ? get_class($resolvedValue)
                    : gettype($resolvedValue)
            )
        );
    }
}