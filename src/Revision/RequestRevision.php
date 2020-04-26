<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Revision;

interface RequestRevision
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