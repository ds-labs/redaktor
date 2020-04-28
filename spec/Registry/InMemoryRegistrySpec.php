<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\InvalidVersionDefinitionException;
use DSLabs\Redaktor\Revision\Revision;
use PhpSpec\ObjectBehavior;
use spec\DSLabs\Redaktor\Double\Revision\DummyRequestRevision;
use spec\DSLabs\Redaktor\Double\Revision\DummyResponseRevision;
use spec\DSLabs\Redaktor\Double\Revision\DummyRoutingRevision;

/**
 * @see InMemoryRegistry
 */
class InMemoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryRegistry::class);
    }

    function it_retrieves_an_empty_list_of_revision_definitions_if_the_registry_is_empty()
    {
        // Arrange
        $this->beConstructedWith([]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(0);
    }

    function it_supports_class_name_revision_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                DummyRoutingRevision::class,
                DummyRequestRevision::class,
                DummyResponseRevision::class,
            ],
        ]);

        // Assert
        $this->shouldNotThrow(\Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_supports_closure_revision_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionDefinition = static function() { },
            ]
        ]);

        // Assert
        $this->shouldNotThrow(\Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_disallow_non_class_name_strings()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                'bar',
            ]
        ]);

        // Assert
        $this->shouldThrow(InvalidVersionDefinitionException::class)
            // Act
            ->duringInstantiation();
    }

    function it_retrieves_all_revisions_from_versions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionA = DummyRequestRevision::class,
                $revisionB = DummyRoutingRevision::class,
            ],
            'bar' => [
                $revisionC = DummyResponseRevision::class,
            ],
        ]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldHaveCount(3);
        $revisions->shouldBe([
            $revisionA,
            $revisionB,
            $revisionC,
        ]);
    }

    function it_retrieves_revisions_since_a_given_version()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionA = DummyRequestRevision::class,
            ],
            'bar' => [
                $revisionB = DummyRequestRevision::class,
                $revisionC = DummyRequestRevision::class,
            ],
            'baz' => [
                $revisionD = DummyRequestRevision::class,
            ],
        ]);

        // Act
        $revisions = $this->retrieveSince('bar');

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(3);
        $revisions->shouldBe([
            $revisionB,
            $revisionC,
            $revisionD,
        ]);
    }

    function it_disallows_empty_version_definitions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [],
        ]);

        // Assert
        $this->shouldThrow(InvalidVersionDefinitionException::class)
            // Act
            ->duringInstantiation();
    }
}
