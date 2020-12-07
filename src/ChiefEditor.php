<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Department\GenericMessageDepartment;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditor;
use DSLabs\Redaktor\Editor\RoutingEditor;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\RevisionDefinition;
use DSLabs\Redaktor\Registry\RevisionResolver;
use DSLabs\Redaktor\Registry\SimpleRevisionResolver;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;

final class ChiefEditor implements ChiefEditorInterface
{
    /**
     * @var Registry
     */
    private $registry;

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
        RevisionResolver $revisionResolver = null
    ) {
        $this->registry = $registry;
        $this->revisionResolver = $revisionResolver ?? new SimpleRevisionResolver();
        $this->editorProvider = new GenericMessageDepartment();
    }

    /**
     * @inheritDoc
     */
    public function listVersions(): array
    {
        return $this->registry->index();
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
     * Appoint an editor for the given $version to carry out the work.
     *
     * @return RoutingEditor|MessageEditor
     */
    public function appointEditor(Version $version): EditorInterface
    {
        return $this->editorProvider->provideEditor(
            $this->createBrief($version)
        );
    }

    /**
     * Create the brief for the given $version.
     */
    private function createBrief(Version $version): Brief
    {
        $revisionsDefinitions = $this->registry->retrieveSince($version);

        return new Brief(
            $version,
            $this->open($revisionsDefinitions)
        );
    }

    /**
     * @param RevisionDefinition[] $revisionDefinitions
     *
     * @return Revision[]
     */
    private function open(array $revisionDefinitions): array
    {
        return array_map(function (RevisionDefinition $revisionDefinition): Revision {
            return $this->revisionResolver->resolve($revisionDefinition);
        }, $revisionDefinitions);
    }
}
