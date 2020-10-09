<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Registry\RevisionDefinition;
use DSLabs\Redaktor\Registry\UnableToResolveRevisionDefinition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use spec\DSLabs\Redaktor\Double\Revision\DummyRevision;

/**
 * @see PSR11RevisionResolver
 */
final class PSR11RevisionResolverSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_resolves_an_instance_revision_definition()
    {
        // Arrange
        $definition = new RevisionDefinition(
            $revision = new DummyRevision()
        );

        // Act
        $this->resolve($definition)
            // Assert
            ->shouldBe($revision);
    }

    function it_resolves_a_string_revision_definition_out_of_the_container(
        ContainerInterface $container
    ) {
        // Arrange
        $definition = new RevisionDefinition(DummyRevision::class);
        $container->get(Argument::any())->willReturn($revision = new DummyRevision());

        // Act
        $this->resolve($definition)
            // Assert
            ->shouldBe($revision);
    }

    function it_throws_an_exception_if_a_string_revision_definition_does_not_resolve_to_a_revision_instance(
        ContainerInterface $container
    ) {
        // Arrange
        $definition = new RevisionDefinition(DummyRevision::class);
        $container->get(Argument::any())->willReturn(new \stdClass());

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('resolve', [$definition]);
    }

    function it_throws_an_exception_if_a_string_revision_definition_has_not_been_registered_in_the_container(
        ContainerInterface $container
    ) {
        // Arrange
        $exception = new class extends \Exception implements NotFoundExceptionInterface {};
        $container->get(Argument::any())->willThrow($exception);
        $definition = new RevisionDefinition(DummyRevision::class);

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('resolve', [$definition]);
    }

    function it_throws_an_exception_if_resolving_a_string_revision_definition_results_in_an_ContainerExceptionInterface_while_retrieving_the_entry(
        ContainerInterface $container
    ) {
        // Arrange
        $exception = new class extends \Exception implements ContainerExceptionInterface {};
        $container->get(Argument::any())->willThrow($exception);
        $definition = new RevisionDefinition(DummyRevision::class);

        // Assert
        $this->shouldThrow(UnableToResolveRevisionDefinition::class)
            // Act
            ->during('resolve', [$definition]);
    }
}