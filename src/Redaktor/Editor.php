<?php

declare(strict_types=1);

namespace Redaktor;

use Closure;
use Exception\MutationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Editor
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var VersionResolver
     */
    private $versionResolver;

    public function __construct(
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        $this->registry = $registry;
        $this->versionResolver = $versionResolver;
    }

    public function editRequest(RequestInterface $request): RequestInterface
    {
        $originalRequest = $request;
        $version = $this->versionResolver->resolve($originalRequest);

        $revisionFactories = (null === $version)
            ? $this->registry->retrieveAll()
            : $this->registry->retrieveSince($version);

        return array_reduce($revisionFactories, function (RequestInterface $requestToRevise, Closure $revisionFactory) {
            /** @var Revision $revision */
            $revision = $revisionFactory();

            if ($revision->isApplicable($requestToRevise)) {
                $currentRequest = $revision->applyToRequest($requestToRevise);

                if ($currentRequest === $requestToRevise) {
                    throw MutationException::inRevision($revision);
                }

                return $currentRequest;
            }

            return $requestToRevise;
        }, $originalRequest);
    }

    public function editResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $version = $this->versionResolver->resolve($request);

        $revisionFactories = (null === $version)
            ? $this->registry->retrieveAll()
            : $this->registry->retrieveSince($version);

        $currentRequest = $request;
        $lastResponse = $response;
        foreach (array_reverse($revisionFactories) as $revisionFactory) {
            /** @var Revision $revision */
            $revision = $revisionFactory();

            if ($revision->isApplicable($currentRequest)) {
                $currentResponse = $revision->applyToResponse($lastResponse);
                $currentRequest = $revision->applyToRequest($currentRequest);

                if ($lastResponse === $currentResponse) {
                    throw MutationException::inRevision($revision);
                }

                $lastResponse = $currentResponse;
            }
        }

        return $lastResponse;
    }
}
