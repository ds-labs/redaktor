<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditor;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\RevisionDefinition;
use DSLabs\Redaktor\Registry\RevisionResolver;
use DSLabs\Redaktor\Registry\UnableToResolveRevisionDefinition;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;

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

    function it_appoints_a_message_editor_by_default()
    {
        // Act
        $editor = $this->appointEditor($version = new Version('foo'));

        // Assert
        $editor->shouldBeAnInstanceOf(MessageEditor::class);
        $editor->briefedVersion()->shouldBe($version);
    }

    function it_appoints_an_editor_for_a_request_with_no_defined_version()
    {
        // Act
        $editor = $this->appointEditor(new Version('foo'));

        // Assert
        $editor->briefedRevisions()
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
        $editor->briefedRevisions()->shouldBe([
            $revisionA,
            $revisionB,
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

    function it_speaks_to_an_editor_provider_to_appoint_an_specialised_editor(
        EditorProvider $editorProvider,
        EditorInterface $specialisedEditor
    ) {
        // Arrange
        $editorProvider->provideEditor(Argument::any())
            ->willReturn($specialisedEditor);

        // Act
        $editor = $this->speakTo($editorProvider)
            ->appointEditor(new Version('foo'));

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
