<?php

declare(strict_types=1);

namespace Redaktor;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Revision
{
    public function isApplicable(RequestInterface $request): bool;

    public function applyToRequest(RequestInterface $request): RequestInterface;

    public function applyToResponse(ResponseInterface $response): ResponseInterface;
}
