<?php

namespace spec\DSLabs\Redaktor\Version;

use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\UnresolvedVersionException;
use DSLabs\Redaktor\Version\Version;
use DSLabs\Redaktor\Version\VersionResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use spec\DSLabs\Redaktor\Double\DummyRequest;

/**
 * @see VersionResolver
 */
class VersionResolverSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_initialised_with_a_non_strategy_instance()
    {
        // Arrange
        $this->beConstructedWith([
            new \stdClass(),
        ]);

        // Assert
        $this->shouldThrow(\InvalidArgumentException::class)
            // Act
            ->duringInstantiation();
    }

    function it_throws_an_exception_if_there_are_no_strategies_registered()
    {
        // Arrange
        $this->beConstructedWith([]);
        $request = new DummyRequest();

        // Assert
        $this->shouldThrow(UnresolvedVersionException::class)
            // Act
            ->during('resolve', [$request]);
    }

    function it_resolves_to_a_version_using_the_registered_strategy(Strategy $strategy)
    {
        // Arrange
        $strategy->resolve(Argument::any())
            ->willReturn($expectedVersion = new Version('foo'));

        $request = new DummyRequest();
        $this->beConstructedWith([
            $strategy,
        ]);

        // Act
        $version = $this->resolve($request);

        // Assert
        $version->shouldBe($expectedVersion);
        $strategy->resolve($request)
            ->shouldHaveBeenCalled();
    }

    function it_resolves_to_the_version_provided_by_the_first_resolved_strategy(
        Strategy $unresolvedVersionStrategy,
        Strategy $resolvedVersionStrategy
    ) {
        // Arrange
        $unresolvedVersionStrategy->resolve(Argument::any())
            ->willThrow(UnresolvedVersionException::class);
        $resolvedVersionStrategy->resolve(Argument::any())
            ->willReturn($expectedVersion = new Version('foo'));
        $this->beConstructedWith([
            $unresolvedVersionStrategy,
            $resolvedVersionStrategy,
        ]);

        // Act
        $this->resolve($request = new \stdClass())
            // Assert
            ->shouldReturn($expectedVersion);

        $unresolvedVersionStrategy->resolve($request)
            ->shouldHaveBeenCalled();
        $resolvedVersionStrategy->resolve($request)
            ->shouldHaveBeenCalled();
    }
}
