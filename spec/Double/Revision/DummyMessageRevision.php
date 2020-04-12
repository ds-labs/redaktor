<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Double\Revision;

use DSLabs\Redaktor\Registry\MessageRevision;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class DummyMessageRevision implements MessageRevision
{
    public function isApplicable(RequestInterface $request): bool { }

    public function applyToRequest(RequestInterface $request): RequestInterface { }

    public function applyToResponse(ResponseInterface $response): ResponseInterface { }
}