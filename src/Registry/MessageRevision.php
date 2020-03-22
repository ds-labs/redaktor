<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface MessageRevision
{
    public function isApplicable(RequestInterface $request): bool;

    public function applyToRequest(RequestInterface $request): RequestInterface;

    public function applyToResponse(ResponseInterface $response): ResponseInterface;
}
