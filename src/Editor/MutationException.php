<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\Revision;

final class MutationException extends \Exception
{
    /**
     * @param Revision $revision
     * @return self
     */
    public static function inRevision(Revision $revision): self
    {
        // @todo: Add `RevisionGuard` to ensure $revision is `RequestRevision` or `ResponseRevision`.
        $revisionClassName = get_class($revision);
        $message = "Revision [{$revisionClassName}] returned same received instance. Revision must be immutable.";

        return new self($message);
    }
}