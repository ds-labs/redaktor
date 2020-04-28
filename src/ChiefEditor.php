<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use Closure;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\Supersedes;
use DSLabs\Redaktor\Version\VersionResolver;

final class ChiefEditor implements ChiefEditorInterface
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
    public function appointEditor(object $request): EditorInterface
    {
        return new Editor(
            $this->createBrief($request)
        );
    }

    private function createBrief(object $request): Brief
    {
        $version = $this->askForCurrentVersion($request);
        $revisions = $this->getRevisionsForVersion($version);
        $revisionInstances = self::filterSupersededRevisions(
            self::open($revisions)
        );

        return new Brief(
            $request,
            $revisionInstances
        );
    }

    private function askForCurrentVersion(object $request): ?string
    {
        return $this->versionResolver->resolve($request);
    }

    private function getRevisionsForVersion(?string $version): array
    {
        return $version === null
            ? $this->registry->retrieveAll()
            : $this->registry->retrieveSince($version);
    }

    /**
     * @param Closure[]|string[] $revisionDefinitions
     *
     * @return Revision[]
     */
    private static function open(array $revisionDefinitions): array
    {
        return array_map(static function ($revisionDefinition) {

            if ($revisionDefinition instanceof Closure) {
                return $revisionDefinition();
            }

            return new $revisionDefinition();
        }, $revisionDefinitions);
    }

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
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
            static function (array $squashedRevisions, Revision $currentRevision): array {

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
