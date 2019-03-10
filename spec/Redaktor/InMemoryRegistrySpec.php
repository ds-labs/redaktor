<?php

declare(strict_types=1);

namespace spec\Redaktor;

use Exception\InvalidVersionDefinitionException;
use PhpSpec\ObjectBehavior;
use Redaktor\InMemoryRegistry;
use Redaktor\Revision;

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
    ) {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionA = function(): Revision {},
                $revisionB = function(): Revision {},
            ],
            'bar' => [
                $revisionC = function(): Revision {},
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

    function it_returns_revisions_since_a_given_version_onwards()
{
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $revisionA = function(): Revision {},
            ],
            'bar' => [
                $revisionB = function(): Revision {},
                $revisionC = function(): Revision {},
            ],
            'baz' => [
                $revisionD = function(): Revision {},
            ],
        ]);

        // Act
        $revisions = $this->retrieveSince('bar');

        // Assert
        $revisions->shouldBeArray();
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
}
