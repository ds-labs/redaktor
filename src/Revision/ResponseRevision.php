<?php

namespace DSLabs\Redaktor\Revision;

interface ResponseRevision extends Revision
{
    /**
     * Evaluates if the request is applicable to current revision.
     */
    public function isApplicable(object $request): bool;

    /**
     * Apply revision to the given response.
     * An instance of the request revised up to this point is passed in for convenience.
     */
    public function applyToResponse(object $request, object $response): object;
}
