<?php

declare(strict_types=1);

namespace Redaktor;

use Psr\Http\Message\RequestInterface;

final class QueryStringVersionResolver implements VersionResolver
{
    /**
     * @var string
     */
    private $parameterName;

    public function __construct(string $parameterName)
    {
        $this->parameterName = $parameterName;
    }

    public function resolve(RequestInterface $request): ?string
    {
        $queryString = $request->getUri()
            ->getQuery();

        if ('' === $queryString) {
            return null;
        }

        $parameters = explode('&', $queryString);
        foreach ($parameters as $parameter) {
            [$key, $value] = explode('=', $parameter);

            if ($this->parameterName === $key) {
                return $value;
            }
        }

        return null;
    }
}
