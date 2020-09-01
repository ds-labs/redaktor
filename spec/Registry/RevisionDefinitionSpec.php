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
    function it_cannot_be_instantiated_with_the_name_of_a_class_that_does_not_implement_the_revision_interface()
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

    function it_accepts_a_class_name()
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

    function it_accepts_a_closure()
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

    function it_cannot_be_instantiated_with_a_null()
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

    function it_cannot_be_instantiated_with_an_integer()
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

    function it_supports_a_revision_class_name()
    {
        // Arrange
        $this->beConstructedWith(
            DummyRevision::class
        );

        // Act
        $factory = $this->getFactory();

        // Assert
        $factory()->shouldBe(
            DummyRevision::class
        );
    }

    function it_supports_a_revision_instance()
    {
        // Arrange
        $this->beConstructedWith(
            $revision = new DummyRevision()
        );

        // Act
        $factory = $this->getFactory();

        // Assert
        $factory()->shouldBe(
            $revision
        );
    }

    function it_uses_the_closure_definition_as_the_factory()
    {
        // Arrange
        $this->beConstructedWith(
            $definition = static function () {}
        );

        // Act
        $factory = $this->getFactory();

        // Assert
        $factory->shouldBe(
            $definition
        );
    }

    function it_creates_the_factory_only_once()
    {
        // Arrange
        $this->beConstructedWith(
            DummyRevision::class
        );

        // Act
        $factoryA = $this->getFactory();
        $factoryB = $this->getFactory();

        // Assert
        $factoryA->shouldBe($factoryB);
    }
}
