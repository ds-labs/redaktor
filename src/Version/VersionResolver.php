<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Version;

interface VersionResolver
{
    public function resolve(object $request): ?string;
}