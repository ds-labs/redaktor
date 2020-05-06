<?php

namespace DSLabs\Redaktor\Revision;

interface RequestRevision extends Revision
{
    /**
     * Evaluates if the request is applicable to current revision.
     */
    public function isApplicable(object $request): bool;

    /**
     * Apply revision to the given request.
     */
    public function applyToRequest(object $request): object;
}