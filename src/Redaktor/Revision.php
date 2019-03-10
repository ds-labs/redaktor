<?php

namespace Redaktor;

use Psr\Http\Message\RequestInterface;

interface Revision
{
    public function isApplicable(RequestInterface $request): bool;

    public function applyToRequest(RequestInterface $request): RequestInterface;

    public function applyToResponse($argument1);
}
