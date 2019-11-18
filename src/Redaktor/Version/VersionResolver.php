<?php

declare(strict_types=1);

namespace Redaktor\Version;

use Psr\Http\Message\RequestInterface;

interface VersionResolver
{
    public function resolve(RequestInterface $request): ?string;
}