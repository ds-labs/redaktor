<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Exception\InvalidVersionDefinitionException;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use PhpSpec\ObjectBehavior;

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

    function it_retrieves_all_factories_in_the_registry()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $factoryA = static function () {},
                $factoryB = static function () {},
            ],
            'bar' => [
                $factoryC = static function () {},
            ],
        ]);

        // Act
        $revisions = $this->retrieveAll();

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(3);
        $revisions->shouldBe([
            $factoryA,
            $factoryB,
            $factoryC,
        ]);
    }

    function it_retrieves_factories_since_a_given_version()
    {
        // Arrange
        $this->beConstructedWith([
            'foo' => [
                $factoryA = static function () {},
            ],
            'bar' => [
                $factoryB = static function () {},
                $factoryC = static function () {},
            ],
            'baz' => [
                $factoryD = static function () {},
            ],
        ]);

        // Act
        $revisions = $this->retrieveSince('bar');

        // Assert
        $revisions->shouldBeArray();
        $revisions->shouldHaveCount(3);
        $revisions->shouldBe([
            $factoryB,
            $factoryC,
            $factoryD,
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

    function it_disallows_a_string_as_a_revision()
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
