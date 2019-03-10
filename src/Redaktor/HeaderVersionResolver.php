<?php

declare(strict_types=1);

namespace Redaktor;

use Psr\Http\Message\RequestInterface;

final class HeaderVersionResolver implements VersionResolver
{
    /**
     * @var string
     */
    private $headerName;

    public function __construct(string $headerName)
    {
        $this->headerName = $headerName;
    }

    public function resolve(RequestInterface $request): ?string
    {
        $version = $request->getHeaderLine($this->headerName);

        return '' !== trim($version)
            ? $version
            : null;
    }
}
