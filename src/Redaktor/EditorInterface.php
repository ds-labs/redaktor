<?php

declare(strict_types=1);

namespace Redaktor;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface EditorInterface
{
    public function reviseRequest(ServerRequestInterface $request): ServerRequestInterface;

    public function reviseResponse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}