<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

interface MessageRevision
{
    public function isApplicable(object $request): bool;

    public function applyToRequest(object $request): object;

    public function applyToResponse(object $response): object;
}
