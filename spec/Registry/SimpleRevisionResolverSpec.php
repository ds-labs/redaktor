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
            static function () use ($revision) {
                return $revision->getWrappedObject();
            }
        );

        // Act
        $resolvedRevision = $this->resolve($revisionDefinition);

        // Assert
        $resolvedRevision->shouldBe($revision);
    }

    function it_fails_if_the_definition_factory_returns_the_name_of_a_class_that_does_not_implement_the_revision_interface()
    {
        // Arrange
        $revisionDefinition = new RevisionDefinition(
            static function () {
                return \stdClass::class;
            }
        );

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('resolve', [$revisionDefinition]);
    }
}
