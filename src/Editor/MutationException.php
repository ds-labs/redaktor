<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;

final class MutationException extends \Exception
{
    /**
     * @param RequestRevision|ResponseRevision $revision
     * @return self
     */
    public static function inRevision($revision): self
    {
        // @todo: Add `RevisionGuard` to ensure $revision is `RequestRevision` or `ResponseRevision`.
        $revisionClassName = get_class($revision);
        $message = "Revision [{$revisionClassName}] returned same received instance. Revision must be immutable.";

        return new self($message);
    }
}