<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Registry\RevisionDefinition;
use DSLabs\Redaktor\Registry\SimpleRevisionResolver;
use DSLabs\Redaktor\Registry\UnableToResolveRevisionDefinition;
use DSLabs\Redaktor\Revision\Revision;
use PhpSpec\ObjectBehavior;
use spec\DSLabs\Redaktor\Double\Revision\DummyRevision;

/**
 * @see SimpleRevisionResolver
 */
class SimpleRevisionResolverSpec extends ObjectBehavior
{
    function it_resolves_class_name_revision_defintions()
    {
        // Arrange
        $revisionDefinition = new RevisionDefinition(
            DummyRevision::class
        );

        // Act
        $revision = $this->resolve($revisionDefinition);

        // Assert
        $revision->shouldBeAnInstanceOf(DummyRevision::class);
    }

    function it_resolves_closure_revision_definitions(
        Revision $revision
    ) {
        // Arrange
        $revisionDefinition = new RevisionDefinition(
            static function () use ($revision): Revision {
                return $revision->getWrappedObject();
            }
        );

        // Act
        $resolvedRevision = $this->resolve($revisionDefinition);

        // Assert
        $resolvedRevision->shouldBe($revision);
    }

    function it_throws_an_exception_if_the_revision_definition_returns_a_class_name_that_does_not_implement_the_revision_interface()
    {
        // Arrange
        $revisionDefinition = new RevisionDefinition(
            static function (): string {
                return \stdClass::class;
            }
        );

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('resolve', [$revisionDefinition]);
    }

    function it_throws_an_exception_if_the_revision_definition_returns_a_non_instantiable_string()
    {
        // Arrange
        $revisionDefinition = new RevisionDefinition(
            static function (): string {
                return 'foo';
            }
        );

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('resolve', [$revisionDefinition]);
    }
}
