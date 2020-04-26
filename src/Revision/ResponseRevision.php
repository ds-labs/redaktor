<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Revision;

interface ResponseRevision
{
    /**
     * Evaluates if the request is applicable to current revision.
     */
    public function isApplicable(object $request): bool;

    /**
     * Apply revision to the given response.
     */
    public function applyToResponse(object $response): object;
}
