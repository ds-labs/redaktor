<?php

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Revision\Revision;

interface RevisionResolver
{
    /**
     * Resolves a `RevisionDefinition` into a Revision instance.
     *
     * @throws UnableToResolveRevisionDefinition If unable to resolve the revision definition.
     */
    public function resolve(RevisionDefinition $revisionDefinition): Revision;
}