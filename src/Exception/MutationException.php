<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Exception;

use DSLabs\Redaktor\Revision\MessageRevision;

final class MutationException extends \Exception
{
    public static function inRevision(MessageRevision $revision): self
    {
        $revisionClassName = get_class($revision);
        $message = "Revision [{$revisionClassName}] returned same received instance. Revision must be immutable.";

        return new self($message);
    }
}