<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Registry\InvalidRevisionDefinition;
use DSLabs\Redaktor\Registry\RevisionDefinition;
use PhpSpec\ObjectBehavior;
use spec\DSLabs\Redaktor\Double\Revision\DummyRevision;

/**
 * @see RevisionDefinition
 */
class RevisionDefinitionSpec extends ObjectBehavior
{
    function it_does_not_support_an_non_revision_class_name()
    {
        // Arrange
        $this->beConstructedWith(
            \stdClass::class
        );

        // Assert
        $this->shouldThrow(InvalidRevisionDefinition::class)
            // Act
            ->duringInstantiation();
    }

    function it_supports_a_revision_class_name()
    {
        // Arrange
        $this->beConstructedWith(
            DummyRevision::class
        );

        // Assert
        $this->shouldNotThrow(\Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_supports_a_closure()
    {
        // Arrange
        $this->beConstructedWith(
            static function () {}
        );

        // Assert
        $this->shouldNotThrow(\Throwable::class)
            // Act
            ->duringInstantiation();
    }

    function it_does_not_support_a_null()
    {
        // Arrange
        $this->beConstructedWith(
            null
        );

        // Assert
        $this->shouldThrow(InvalidRevisionDefinition::class)
            // Act
            ->duringInstantiation();
    }

    function it_does_not_support_an_integer()
    {
        // Arrange
        $this->beConstructedWith(
            4
        );

        // Assert
        $this->shouldThrow(InvalidRevisionDefinition::class)
            // Act
            ->duringInstantiation();
    }

    function it_does_not_support_a_non_existing_class_name()
    {
        // Arrange
        $this->beConstructedWith(
            'foo'
        );

        // Assert
        $this->shouldThrow(InvalidRevisionDefinition::class)
            // Act
            ->duringInstantiation();
    }

    function it_does_not_support_an_existing_class_name_that_is_not_a_revision_instance()
    {
        // Arrange
        $this->beConstructedWith(
            \stdClass::class
        );

        // Assert
        $this->shouldThrow(InvalidRevisionDefinition::class)
            // Act
            ->duringInstantiation();
    }

    function it_resolves_a_revision_class_name()
    {
        // Arrange
        $this->beConstructedWith(
            DummyRevision::class
        );

        // Act
        $this()
            // Assert
            ->shouldBe(
                DummyRevision::class
            );
    }

    function it_resolves_a_revision_instance()
    {
        // Arrange
        $this->beConstructedWith(
            $revision = new DummyRevision()
        );

        // Act
        $this()
            // Assert
            ->shouldBe(
                $revision
            );
    }

    function it_resolves_a_closure_providing_a_class_name()
    {
        // Arrange
        $this->beConstructedWith(
            $definition = static function (): string {
                return 'foo';
            }
        );

        // Act
        $this()
            // Assert
            ->shouldBe('foo');
    }

    function it_resolves_a_closure_providing_a_revision_instance()
    {
        // Arrange
        $revision = new DummyRevision();
        $this->beConstructedWith(
            $definition = static function () use ($revision): DummyRevision {
                return $revision;
            }
        );

        // Act
        $this()
            // Assert
            ->shouldBe($revision);
    }

    function it_caches_the_resolved_a_closure_definition()
    {
        // Arrange
        $this->beConstructedWith(
            static function () {
                return random_int(0, 999);
            }
        );

        // Act
        $factoryA = $this();
        $factoryB = $this();

        // Assert
        $factoryA->shouldBe($factoryB);
    }
}
