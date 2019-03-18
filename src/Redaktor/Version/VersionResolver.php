<?php

namespace Redaktor\Version;

use Psr\Http\Message\RequestInterface;

interface VersionResolver
{
    public function resolve(RequestInterface $request): ?string;
}