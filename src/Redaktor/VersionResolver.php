<?php

namespace Redaktor;

use Psr\Http\Message\RequestInterface;

interface VersionResolver
{
    public function resolve(RequestInterface $request): ?string;
}