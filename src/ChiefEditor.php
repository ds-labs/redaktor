<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Department\EditorDepartment;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\RevisionDefinition;
use DSLabs\Redaktor\Registry\RevisionResolver;
use DSLabs\Redaktor\Registry\SimpleRevisionResolver;
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

    /**
     * @var RevisionResolver|null
     */
    private $revisionResolver;

    /**
     * @var EditorProvider
     */
    private $editorProvider;

    public function __construct(
        Registry $registry,
        VersionResolver $versionResolver,
        RevisionResolver $revisionResolver = null
    ) {
        $this->registry = $registry;
        $this->versionResolver = $versionResolver;
        $this->revisionResolver = $revisionResolver ?? new SimpleRevisionResolver();
        $this->editorProvider = new EditorDepartment();
    }

    /**
     * Get in touch with an editor provider, who will provide an specialised editor.
     */
    public function speakTo(EditorProvider $editorProvider): ChiefEditorInterface
    {
        $this->editorProvider = $editorProvider;

        return $this;
    }

    /**
     * Create the brief and request the editor that will carry out the work.
     */
    public function appointEditor(object $request): EditorInterface
    {
        return $this->editorProvider->provideEditor(
            $this->createBrief($request)
        );
    }

    private function createBrief(object $request): Brief
    {
        $version = $this->askForCurrentVersion($request);
        $revisionsDefinitions = $this->getRevisionDefinitionsSinceVersion($version);

        $revisions = self::filterRevisions(
            $this->open($revisionsDefinitions)
        );

        return new Brief(
            $request,
            $revisions
        );
    }

    private function askForCurrentVersion(object $request): ?string
    {
        return $this->versionResolver->resolve($request);
    }

    private function getRevisionDefinitionsSinceVersion(?string $version): array
    {
        return $version === null
            ? $this->registry->retrieveAll()
            : $this->registry->retrieveSince($version);
    }

    /**
     * @param RevisionDefinition[] $revisionDefinitions
     *
     * @return Revision[]
     */
    private function open(array $revisionDefinitions): array
    {
        return array_map(function (RevisionDefinition $revisionDefinition) {

            return $this->revisionResolver->resolve($revisionDefinition);
        }, $revisionDefinitions);
    }

    /**
     * @param Revision[] $revisions
     *
     * @return Revision[]
     */
    private static function filterRevisions(array $revisions): array
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
