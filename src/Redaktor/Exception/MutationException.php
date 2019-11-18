<?php

declare(strict_types=1);

namespace Redaktor\Exception;

use Redaktor\Revision;

final class MutationException extends \Exception
{
    public static function inRevision(Revision $revision): self
    {
        $revisionClassName = get_class($revision);
        $message = "Revision [{$revisionClassName}] returned same received instance. Revision must be immutable.";

        return new self($message);
    }
}