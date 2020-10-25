<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\RevisionDefinition;
use DSLabs\Redaktor\Registry\RevisionResolver;
use DSLabs\Redaktor\Registry\UnableToResolveRevisionDefinition;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\Supersedes;
use DSLabs\Redaktor\Version\Version;
use DSLabs\Redaktor\Version\VersionResolver;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use spec\DSLabs\Redaktor\Double\DummyRequest;

/**
 * @see ChiefEditor
 */
class ChiefEditorSpec extends ObjectBehavior
{
    function let(
        Registry $registry
    ) {
        $registry->retrieveSince(Argument::any())->willReturn([]);
        $this->beConstructedWith($registry);
    }

    function it_appoints_a_generic_editor()
    {
        // Act
        $editor = $this->appointEditor($version = new Version('foo'));

        // Assert
        $editor->shouldBeAnInstanceOf(Editor::class);
        $editor->briefedVersion()->shouldBe($version);
    }

    function it_appoints_an_editor_for_a_request_with_no_defined_version()
    {
        // Act
        $editor = $this->appointEditor(new Version('foo'));

        // Assert
        $editor->retrieveBriefedRevisions()
            ->shouldHaveCount(0);
    }

    function it_briefs_the_appointed_editor_with_the_details(
        Registry $registry,
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $registry->retrieveSince(Argument::any())->willReturn([
            self::createRevisionDefinition($revisionA),
            self::createRevisionDefinition($revisionB),
        ]);

        // Act
        $editor = $this->appointEditor(new Version('foo'));

        // Assert
        $editor->retrieveBriefedRevisions()->shouldBe([
            $revisionA,
            $revisionB,
        ]);
    }

    function it_discards_superseded_revisions(
        Registry $registry,
        VersionResolver $versionResolver,
        Revision $supersededRevision,
        Revision $supersederRevision,
        RevisionResolver $revisionResolver
    ) {
        // Arrange
        $this->beConstructedWith(
            $registry,
            $revisionResolver
        );

        $supersederRevision->implement(Supersedes::class);
        $supersederRevision->supersedes(Argument::any())->willReturn(true);

        $registry->retrieveSince(Argument::any())->willReturn([
            self::createRevisionDefinition($supersederRevision),
            self::createRevisionDefinition($supersededRevision),
        ]);

        $revisionResolver->resolve(Argument::any())->willReturn($supersededRevision, $supersederRevision);

        // Act
        $editor = $this->appointEditor(new Version('foo'));

        // Assert
        $editor->retrieveBriefedRevisions()->shouldBe([
            $supersederRevision,
        ]);
    }

    function it_delegates_the_revision_instantiation_to_the_revision_resolver(
        Registry $registry,
        RevisionResolver $revisionResolver,
        Revision $revision
    ) {
        // Arrange
        $this->beConstructedWith($registry, $revisionResolver);

        $registry->retrieveSince(Argument::any())->willReturn([
            $revisionDefinition = self::createRevisionDefinition($revision),
        ]);
        $revisionResolver->resolve(Argument::any())->willReturn($revision);

        // Act
        $this->appointEditor(new Version('foo'));

        // Assert
        $revisionResolver->resolve($revisionDefinition)
            ->shouldHaveBeenCalled();
    }

    function it_bubbles_up_the_exception_thrown_by_the_resolver_if_unable_to_resolve_the_revision_definition(
        Registry $registry,
        RevisionResolver $revisionResolver,
        Revision $revision
    ) {
        // Arrange
        $this->beConstructedWith($registry, $revisionResolver);

        $registry->retrieveSince(Argument::any())->willReturn([
            $revisionDefinition = self::createRevisionDefinition($revision),
        ]);

        $revisionResolver->resolve(Argument::any())
            ->willThrow(UnableToResolveRevisionDefinition::class);

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('appointEditor', [new Version('foo')]);
    }

    function it_can_speak_to_an_editor_provider_to_get_an_specialised_editor(
        Registry $registry,
        EditorProvider $editorDepartment,
        EditorInterface $specialisedEditor
    ) {
        // Arrange
        $editorDepartment->provideEditor(Argument::any())->willReturn($specialisedEditor);

        // Act
        $this->speakTo($editorDepartment);
        $editor = $this->appointEditor(new Version('foo'));

        // Assert
        $editor->shouldBe($specialisedEditor);
    }

    private static function createRevisionDefinition(Collaborator $revision): RevisionDefinition
    {
        return new RevisionDefinition(
            static function () use ($revision) {
                return $revision->getWrappedObject();
            }
        );
    }
}
