<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Revision\Revision;

final class SimpleRevisionResolver implements RevisionResolver
{
    /**
     * @inheritDoc
     */
    public function resolve(RevisionDefinition $revisionDefinition): Revision
    {
        $revision = $revisionDefinition();

        if (is_string($revision) && class_exists($revision)) {
            $revision = new $revision();
        }

        if ($revision instanceof Revision) {
            return $revision;
        }

        throw new UnableToResolveRevisionDefinition($revision);
    }
}
