<?php

namespace spec\Redaktor\Version;

use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Redaktor\Version\QueryStringResolver;

class QueryStringResolverSpec extends ObjectBehavior
{
    function let (
        UriInterface $uri,
        RequestInterface $request
    ) {
        $this->beConstructedWith('foo');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryStringResolver::class);
    }

    function it_resolves_not_defined_query_string_parameter_to_null(
        UriInterface $uri,
        RequestInterface $request
    ) {
        // Arrange
        $uri->getQuery()->willReturn('');
        $request->getUri()->willReturn($uri);

        // Act
        $revisionName = $this->resolve($request);

        // Assert
        $revisionName->shouldBeNull();
    }

    function it_resolves_to_revision_name_from_single_query_string_parameter(
        UriInterface $uri,
        RequestInterface $request
    ) {
        // Arrange
        $revisionNameQueryStringParameter = 'foo';
        $revisionName = 'bar';
        $uri->getQuery()->willReturn("{$revisionNameQueryStringParameter}={$revisionName}");
        $request->getUri()->willReturn($uri);

        $this->beConstructedWith($revisionNameQueryStringParameter);

        // Act
        $resolvedRevisionName = $this->resolve($request);

        // Assert
        $resolvedRevisionName->shouldBe($revisionName);
    }

    function it_resolves_to_revision_name_among_mutiple_query_string_parameters(
        UriInterface $uri,
        RequestInterface $request
    ) {
        // Arrange
        $revisionNameQueryStringParameter = 'foo';
        $revisionName = 'bar';
        $uri->getQuery()->willReturn("goo=caz&{$revisionNameQueryStringParameter}={$revisionName}&joo=daz");
        $request->getUri()->willReturn($uri);

        $this->beConstructedWith($revisionNameQueryStringParameter);

        // Act
        $resolvedRevisionName = $this->resolve($request);

        // Assert
        $resolvedRevisionName->shouldBe($revisionName);
    }
}
