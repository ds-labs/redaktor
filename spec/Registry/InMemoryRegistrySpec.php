<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Exception\InvalidVersionDefinitionException;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Revision\MessageRevision;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use PhpSpec\ObjectBehavior;
use spec\DSLabs\Redaktor\Double\Revision\DummyMessageRevision;
use spec\DSLabs\Redaktor\Double\Revision\DummyRequestRevision;
use spec\DSLabs\Redaktor\Double\Revision\DummyResponseRevision;

/**
 * @see InMemoryRegistry
 */
class InMemoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryRegistry::class);
    }

    function it_retrieves_an_empty_array_of_factories_if_the_resgistry_is_empty()
    {
        // Arrange
        $this->beConstructedWith([]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(0);
    }

    function it_supports_class_name_revisions()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                DummyRequestRevision::class,
                DummyResponseRevision::class,
                DummyMessageRevision::class,
            ]
        ]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldHaveCount(3);
        $revisions->shouldBe([
            DummyRequestRevision::class,
            DummyResponseRevision::class,
            DummyMessageRevision::class,
        ]);
    }

    function it_supports_revision_closure_factory()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $factory = static function() { },
            ]
        ]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldHaveCount(1);
        $revisions->shouldBe([
            $factory,
        ]);
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
                $revisionB = DummyMessageRevision::class,
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
                $revisionA = DummyMessageRevision::class,
            ],
            'bar' => [
                $revisionB = DummyMessageRevision::class,
                $revisionC = DummyMessageRevision::class,
            ],
            'baz' => [
                $revisionD = DummyMessageRevision::class,
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
