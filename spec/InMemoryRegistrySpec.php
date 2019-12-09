<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use Closure;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use DSLabs\Redaktor\Exception\InvalidVersionDefinitionException;
use DSLabs\Redaktor\InMemoryRegistry;
use DSLabs\Redaktor\Revision;

/**
 * @see InMemoryRegistry
 */
class InMemoryRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryRegistry::class);
    }

    function it_returns_an_empty_array_of_revisions_if_the_resgistry_is_empty()
    {
        // Arrange
        $this->beConstructedWith([]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(0);
    }

    function it_returns_all_revisions_in_the_registry(
        Revision $revisionA,
        Revision $revisionB,
        Revision $revisionC
    ) {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                self::makeRevisionFactory($revisionA),
                self::makeRevisionFactory($revisionB),
            ],
            'bar' => [
                self::makeRevisionFactory($revisionC),
            ],
        ]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(3);
        $revisions->shouldBe([
            $revisionA,
            $revisionB,
            $revisionC,
        ]);
    }

    function it_returns_revisions_since_a_given_version_onwards(
        Revision $revisionA,
        Revision $revisionB,
        Revision $revisionC,
        Revision $revisionD
    ) {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                self::makeRevisionFactory($revisionA),
            ],
            'bar' => [
                self::makeRevisionFactory($revisionB),
                self::makeRevisionFactory($revisionC),
            ],
            'baz' => [
                self::makeRevisionFactory($revisionD),
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

    function it_throws_an_InvalidVersionDefinitionException_if_instantiated_with_an_empty_version_definition()
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

    function it_throws_an_InvalidVersionDefinitionException_if_revision_is_not_a_closure()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                'bar',
            ],
        ]);

        // Assert
        $this->shouldThrow(InvalidVersionDefinitionException::class)
            // Act
            ->duringInstantiation();
    }

    private static function makeRevisionFactory(Collaborator $revision): Closure
    {
        return static function() use ($revision): Revision {
            return $revision->getWrappedObject();
        };
    }
}
