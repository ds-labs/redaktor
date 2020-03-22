<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use Closure;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\MessageRevision;
use DSLabs\Redaktor\Registry\Supersedes;
use DSLabs\Redaktor\Version\VersionResolver;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Based on the client's Request, appoints an editor to carry our the work.
 */
final class ChiefEditor
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

    /**
     * Create the initial brief and assign it to the editor who will carry out the work.
     */
    public function appointEditor(ServerRequestInterface $request): Editor
    {
        return new Editor(
            $this->createBrief($request)
        );
    }

    private function createBrief(ServerRequestInterface $request): Brief
    {
        $version = $this->askForCurrentVersion($request);

        $revisions = $this->getRevisionsForVersion($version);

        return new Brief(
            $request,
            $revisions
        );
    }

    private function askForCurrentVersion(ServerRequestInterface $request): ?string
    {
        return $this->versionResolver->resolve($request);
    }

    private function getRevisionsForVersion(?string $version): array
    {
        $revisions = $version === null
            ? $this->registry->retrieveAll()
            : $this->registry->retrieveSince($version);

        return self::filterSupersededRevisions(
            self::open($revisions)
        );
    }

    /**
     * @param Closure[] $revisionFactories
     *
     * @return MessageRevision[]
     */
    private static function open(array $revisionFactories): array
    {
        return array_map(static function (Closure $revisionFactory) {
            return $revisionFactory();
        }, $revisionFactories);
    }

    /**
     * @param MessageRevision[] $revisions
     *
     * @return MessageRevision[]
     */
    private static function filterSupersededRevisions(array $revisions): array
    {
        if (empty($revisions)) {
            return $revisions;
        }

        $initialRevision = array_shift($revisions);
        // @todo Ensure `$initialRevision` does not implement `Supersedes` interface.
        return array_reduce(
            $revisions,
            static function (array $squashedRevisions, MessageRevision $currentRevision): array {

                if ($currentRevision instanceof Supersedes
                    && $currentRevision->supersedes(
                        // Previous Revision
                        $squashedRevisions[count($squashedRevisions)-1]
                    )
                ) {
                    array_pop($squashedRevisions);
                }

                $squashedRevisions[] = $currentRevision;

                return $squashedRevisions;
            },
            [$initialRevision]
        );
    }
}
