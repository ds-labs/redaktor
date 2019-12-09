<?php

namespace spec\DSLabs\Redaktor\Version;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use DSLabs\Redaktor\Version\HeaderResolver;

/**
 * @see HeaderResolver
 */
class HeaderResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('foo');
        $this->shouldHaveType(HeaderResolver::class);
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
