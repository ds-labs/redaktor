<?php

namespace spec\Redaktor;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Redaktor\HeaderVersionResolver;

/**
 * @see HeaderVersionResolver
 */
class HeaderVersionResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('foo');
        $this->shouldHaveType(HeaderVersionResolver::class);
    }

    function it_resolves_version(
        RequestInterface $request
    ) {
        // Arrange
        $request->getHeaderLine('foo')->willReturn('bar');
        $this->beConstructedWith('foo');

        // Act
        $version = $this->resolve($request);
        
        // Assert
        $version->shouldBeString();
        $version->shouldBe('bar');
    }

    function it_returns_null_if_header_is_not_specified(
        RequestInterface $request
    ) {
        // Arrange
        $request->getHeaderLine('foo')->willReturn('');
        $this->beConstructedWith('foo');

        // Act
        $version = $this->resolve($request);
        
        // Assert
        $version->shouldBeNull();
    }
}
