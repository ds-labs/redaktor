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
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\Supersedes;
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
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        $registry->retrieveAll()->willReturn([]);
        $this->beConstructedWith($registry, $versionResolver);
    }

    function it_appoints_a_generic_editor()
    {
        // Act
        $editor = $this->appointEditor($request = new DummyRequest());

        // Assert
        $editor->shouldBeAnInstanceOf(Editor::class);
        $editor->retrieveBriefedRequest()->shouldBe($request);
    }

    function it_appoints_an_editor_for_a_request_with_no_defined_version() {
        // Act
        $editor = $this->appointEditor(new DummyRequest());

        // Assert
        $editor->retrieveBriefedRevisions()
            ->shouldHaveCount(0);
    }

    function it_appoints_an_editor_for_a_request_with_a_defined_version(
        Registry $registry,
        VersionResolver $versionResolver,
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $versionResolver->resolve(Argument::any())->willReturn('foo');
        $registry->retrieveSince('foo')->willReturn([
            self::createRevisionDefinition($revisionA),
            self::createRevisionDefinition($revisionB),
        ]);

        // Act
        $editor = $this->appointEditor(new DummyRequest());

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
            $versionResolver,
            $revisionResolver
        );
        $versionResolver->resolve(Argument::any())
            ->willReturn('foo');

        $supersederRevision->implement(Supersedes::class);
        $supersederRevision->supersedes(Argument::any())->willReturn(true);

        $registry->retrieveSince(Argument::any())->willReturn([
            self::createRevisionDefinition($supersederRevision),
            self::createRevisionDefinition($supersededRevision),
        ]);

        $revisionResolver->resolve(Argument::any())->willReturn($supersededRevision, $supersederRevision);

        // Act
        $editor = $this->appointEditor(new DummyRequest());

        // Assert
        $editor->retrieveBriefedRevisions()->shouldBe([
            $supersederRevision,
        ]);
    }

    function it_delegates_the_revision_instantiation_to_the_revision_resolver(
        Registry $registry,
        VersionResolver $versionResolver,
        RevisionResolver $revisionResolver,
        Revision $revision
    ) {
        // Arrange
        $this->beConstructedWith($registry, $versionResolver, $revisionResolver);

        $versionResolver->resolve(Argument::any())
            ->willReturn('foo');

        $registry->retrieveSince(Argument::any())->willReturn([
            $revisionDefinition = self::createRevisionDefinition($revision),
        ]);
        $revisionResolver->resolve(Argument::any())->willReturn($revision);

        // Act
        $this->appointEditor(new DummyRequest());

        // Assert
        $revisionResolver->resolve($revisionDefinition)
            ->shouldHaveBeenCalled();
    }

    function it_can_speak_to_an_editor_provider_to_get_an_specialised_editor(
        Registry $registry,
        EditorProvider $editorDepartment,
        EditorInterface $specialisedEditor
    ) {
        // Arrange
        $registry->retrieveAll()->willReturn([]);
        $editorDepartment->provideEditor(Argument::any())->willReturn($specialisedEditor);

        // Act
        $this->speakTo($editorDepartment);
        $editor = $this->appointEditor(new DummyRequest());

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
