<?php

declare(strict_types=1);

namespace Redaktor;

use Closure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Redaktor\Exception\MutationException;
use Redaktor\Version\VersionResolver;

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

    public function reviseRequest(RequestInterface $request): RequestInterface
    {
        $originalRequest = $request;
        $version = $this->versionResolver->resolve($originalRequest);

        $revisionFactories = (null === $version)
            ? $this->registry->retrieveAll()
            : $this->registry->retrieveSince($version);

        $revisions = array_map(static function (Closure $revisionFactory): Revision {
            return $revisionFactory();
        }, $revisionFactories);

        $revisions = $this->squashRevisions($revisions);

        return array_reduce($revisions, function (RequestInterface $requestToRevise, Revision $revision) {
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

    public function reviseResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
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

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
     *
     */
    private function squashRevisions(array $revisions): array
    {
        // @todo Ensure initial revision does not implement `Supersedes` interface.

        $initial = array_shift($revisions);
        return array_reduce($revisions, static function (array $squashedRevisions, Revision $currentRevision): array {
            /** @var Supersedes $currentRevision */
            if ($currentRevision instanceof Supersedes
                && $currentRevision->supersedes($squashedRevisions[count($squashedRevisions)-1])
            ) {
                $squashedRevisions[count($squashedRevisions)-1] = $currentRevision;
            } else {
                $squashedRevisions[] = $currentRevision;
            }

            return $squashedRevisions;
        }, [$initial]);
    }
}
