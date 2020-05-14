<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\Editor\Brief;
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
use Prophecy\Argument;
use spec\DSLabs\Redaktor\Double\DummyRequest;
use spec\DSLabs\Redaktor\Double\Revision\DummyRevision;

/**
 * @see ChiefEditor
 */
class ChiefEditorSpec extends ObjectBehavior
{
    function let(
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        $this->beConstructedWith($registry, $versionResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChiefEditor::class);
    }

    function it_appoints_editor_for_a_request_with_no_defined_version(
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        // Arrange
        $registry->retrieveAll()->willReturn([]);

        // Act
        $editor = $this->appointEditor($request = new DummyRequest());

        // Assert
        $versionResolver->resolve($request)->shouldHaveBeenCalled();
        $registry->retrieveAll()->shouldHaveBeenCalled();

        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request,
                    []
                )
            )
        );
    }

    function it_appoints_editor_for_a_request_with_a_defined_version(
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        // Arrange
        $versionResolver->resolve(Argument::any())->willReturn('foo');
        $registry->retrieveSince('foo')->willReturn([]);

        // Act
        $editor = $this->appointEditor($request = new DummyRequest());

        // Assert
        $versionResolver->resolve($request)->shouldHaveBeenCalled();
        $registry->retrieveSince('foo')->shouldHaveBeenCalled();

        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request,
                    []
                )
            )
        );
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

        $supersederRevision->implement(Supersedes::class);
        $supersederRevision->supersedes(Argument::any())->willReturn(true);

        $registry->retrieveAll()->willReturn([
            new RevisionDefinition(DummyRevision::class),
            new RevisionDefinition(DummyRevision::class),
        ]);

        $revisionResolver->resolve(Argument::any())->willReturn($supersededRevision, $supersederRevision);

        // Act
        $editor = $this->appointEditor($request = new DummyRequest());

        // Assert
        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request,
                    [
                        $supersederRevision->getWrappedObject(),
                    ]
                )
            )
        );
    }

    function it_delegates_the_revision_instantiation_to_the_revision_resolver(
        Registry $registry,
        VersionResolver $versionResolver,
        RevisionResolver $revisionResolver,
        Revision $revision
    ) {
        // Arrange
        $this->beConstructedWith($registry, $versionResolver, $revisionResolver);

        $registry->retrieveAll()->willReturn([
            $revisionDefinition = new RevisionDefinition(DummyRevision::class),
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
}
