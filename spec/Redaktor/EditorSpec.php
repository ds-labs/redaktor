<?php

declare(strict_types=1);

namespace spec\Redaktor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Redaktor\Editor;
use Redaktor\Exception\MutationException;
use Redaktor\Registry;
use Redaktor\Revision;
use Redaktor\Supersedes;
use Redaktor\Version\VersionResolver;

/**
 * @see Editor
 */
class EditorSpec extends ObjectBehavior
{
    function let(
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        $this->beConstructedWith($registry, $versionResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Editor::class);
    }

    function it_retrieves_all_revisions_from_registry_if_no_version_specified(
        VersionResolver $versionResolver,
        Registry $registry,
        RequestInterface $request
    ) {
        // Arrange
        $versionResolver->resolve($request)->willReturn(null);
        $registry->retrieveAll()->willReturn([]);

        // Act
        $this->reviseRequest($request);

        // Assert
        $registry->retrieveAll()->shouldHaveBeenCalledOnce();
        $registry->retrieveSince(Argument::type('string'))->shouldNotHaveBeenCalled();
    }

    function it_retrieves_revisions_since_resolved_version_to_run_request_through_them(
        VersionResolver $versionResolver,
        Registry $registry,
        RequestInterface $request
    ) {
        // Arrange
        $versionResolver->resolve($request)->willReturn('foo');
        $registry->retrieveSince(Argument::any())->willReturn([]);

        // Act
        $this->reviseRequest($request);

        // Assert
        $registry->retrieveSince('foo')->shouldHaveBeenCalledOnce();
        $registry->retrieveAll()->shouldNotHaveBeenCalled();
    }

    function it_uses_last_revised_request_to_check_if_next_revision_is_applicable(
        Revision $revisionA,
        RequestInterface $requestAfterRevisionA,
        Revision $revisionB,
        RequestInterface $requestAfterRevisionB,
        Registry $registry,
        RequestInterface $originalRequest
    ) {
        // Arrange
        $revisionA->isApplicable(Argument::any())->willReturn(true);
        $revisionA->applyToRequest(Argument::any())->willReturn($requestAfterRevisionA);
        $revisionAFactory = function () use ($revisionA): Revision {
            return $revisionA->getWrappedObject();
        };
        $revisionB->isApplicable(Argument::any())->willReturn(true);
        $revisionB->applyToRequest(Argument::any())->willReturn($requestAfterRevisionB);
        $revisionBFactory = function () use ($revisionB): Revision {
            return $revisionB->getWrappedObject();
        };
        $registry->retrieveAll()->willReturn([
            $revisionAFactory,
            $revisionBFactory,
        ]);

        // Act
        $this->reviseRequest($originalRequest);

        // Assert
        $revisionA->isApplicable($originalRequest)
            ->shouldHaveBeenCalledOnce();
        $revisionB->isApplicable($requestAfterRevisionA)
            ->shouldHaveBeenCalledOnce();
    }

    function it_applies_multiple_revisions_to_the_request(
        RequestInterface $originalRequest,
        RequestInterface $requestAfterRevisionA,
        RequestInterface $requestAfterRevisionB,
        Registry $registry,
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $revisionA->isApplicable(Argument::any())->willReturn(true);
        $revisionA->applyToRequest(Argument::any())->willReturn($requestAfterRevisionA);
        $revisionAFactory = function () use ($revisionA): Revision {
            return $revisionA->getWrappedObject();
        };

        $revisionB->isApplicable(Argument::any())->willReturn(true);
        $revisionB->applyToRequest(Argument::any())->willReturn($requestAfterRevisionB);
        $revisionBFactory = function () use ($revisionB): Revision {
            return $revisionB->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionAFactory,
            $revisionBFactory,
        ]);

        // Act
        $revisedRequest = $this->reviseRequest($originalRequest);

        // Assert
        $revisionA->applyToRequest($originalRequest)
            ->shouldHaveBeenCalledOnce();
        $revisionB->applyToRequest($requestAfterRevisionA)
            ->shouldHaveBeenCalledOnce();
        $revisedRequest->shouldBe($requestAfterRevisionB);
    }

    function it_does_not_apply_unapplicable_revisions_to_the_request(
        RequestInterface $originalRequest,
        Registry $registry,
        Revision $revision
    ) {
        // Arrange
        $revision->isApplicable(Argument::any())->willReturn(false);
        $revisionFactory = function () use ($revision): Revision {
            return $revision->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionFactory,
        ]);

        // Act
        $revisedRequest = $this->reviseRequest($originalRequest);

        // Assert
        $revision->applyToRequest(Argument::any())
            ->shouldNotHaveBeenCalled();
        $revisedRequest->shouldBe($originalRequest);
    }

    function it_retrieves_revisions_since_resolved_version_to_run_response_through_them(
        VersionResolver $versionResolver,
        Registry $registry,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        // Arrange
        $versionResolver->resolve($request)->willReturn('foo');
        $registry->retrieveSince(Argument::any())->willReturn([]);

        // Act
        $this->reviseResponse($request, $response);

        // Assert
        $registry->retrieveSince('foo')->shouldHaveBeenCalledOnce();
        $registry->retrieveAll()->shouldNotHaveBeenCalled();
    }

    function it_does_not_apply_unapplicable_revisions_to_the_response(
        RequestInterface $originalRequest,
        Registry $registry,
        Revision $revision,
        ResponseInterface $upToDateResponse
    ) {
        // Arrange
        $revision->isApplicable(Argument::any())->willReturn(false);
        $revisionFactory = function () use ($revision): Revision {
            return $revision->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionFactory,
        ]);

        // Act
        $revisedResponse = $this->reviseResponse($originalRequest, $upToDateResponse);

        // Assert
        $revisedResponse->shouldBe($upToDateResponse);
        $revision->applyToResponse($upToDateResponse)
            ->shouldNotHaveBeenCalled();
    }

    function it_applies_applicable_revisions_to_response(
        Revision $revision,
        RequestInterface $request,
        ResponseInterface $upToDateResponse,
        ResponseInterface $userExpectedResponse,
        Registry $registry,
        RequestInterface $requestPlaceholder
    ) {
        // Arrange
        $revision->isApplicable($request)->willReturn(true);
        $revision->applyToRequest(Argument::any())->willReturn($requestPlaceholder);
        $revision->applyToResponse(Argument::any())->willReturn($userExpectedResponse);
        $revisionFactory = function () use ($revision): Revision {
            return $revision->getWrappedObject();
        };
        $registry->retrieveAll()->willReturn([
            $revisionFactory
        ]);

        // Act
        $revisedResponse = $this->reviseResponse($request, $upToDateResponse);
        
        // Assert
        $revision->applyToResponse($upToDateResponse)
            ->shouldHaveBeenCalledOnce();
        $revisedResponse->shouldBe($userExpectedResponse);
    }

    function it_applies_revisions_to_response_in_reverse_order(
        Revision $revisionA,
        Revision $revisionB,
        Registry $registry,
        RequestInterface $request,
        ResponseInterface $upToDateResponse,
        ResponseInterface $responseAfterRevisionB,
        ResponseInterface $responseAfterRevisionA,
        RequestInterface $requestPlaceholder
    ) {
        // Arrange
        $revisionA->isApplicable(Argument::any())->willReturn(true);
        $revisionA->applyToRequest(Argument::any())->willReturn($requestPlaceholder);
        $revisionA->applyToResponse(Argument::any())->willReturn($responseAfterRevisionA);
        $revisionAFactory = function () use ($revisionA): Revision {
            return $revisionA->getWrappedObject();
        };

        $revisionB->isApplicable(Argument::any())->willReturn(true);
        $revisionB->applyToRequest(Argument::any())->willReturn($requestPlaceholder);
        $revisionB->applyToResponse(Argument::any())->willReturn($responseAfterRevisionB);
        $revisionBFactory = function () use ($revisionB): Revision {
            return $revisionB->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionAFactory,
            $revisionBFactory
        ]);

        // Act
        $revisedResponse = $this->reviseResponse($request, $upToDateResponse);

        // Assert
        $revisionB->applyToResponse($upToDateResponse)
            ->shouldHaveBeenCalledOnce();
        $revisionA->applyToResponse($responseAfterRevisionB)
            ->shouldHaveBeenCalledOnce();
        $revisedResponse->shouldBe($responseAfterRevisionA);
    }

    function it_uses_last_revised_request_to_check_if_next_revision_is_applicable_to_response(
        Registry $registry,
        Revision $revisionA,
        Revision $revisionB,
        RequestInterface $upToDateRequest,
        ResponseInterface $upToDateResponse,
        RequestInterface $requestAfterRevisionB,
        ResponseInterface $responseAfterRevisionB,
        ResponseInterface $responseAfterRevisionA,
        RequestInterface $placeHolderRequest
    ) {
        // Arrange
        $revisionA->isApplicable(Argument::any())->willReturn(true);
        $revisionA->applyToRequest(Argument::any())->willReturn($placeHolderRequest);
        $revisionA->applyToResponse(Argument::any())->willReturn($responseAfterRevisionA);
        $revisionAFactory = function () use ($revisionA): Revision {
            return $revisionA->getWrappedObject();
        };

        $revisionB->isApplicable(Argument::any())->willReturn(true);
        $revisionB->applyToRequest(Argument::any())->willReturn($requestAfterRevisionB);
        $revisionB->applyToResponse(Argument::any())->willReturn($responseAfterRevisionB);
        $revisionBFactory = function () use ($revisionB): Revision {
            return $revisionB->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionAFactory,
            $revisionBFactory,
        ]);

        // Act
        $this->reviseResponse($upToDateRequest, $upToDateResponse);

        // Assert
        $revisionB->isApplicable($upToDateRequest)
            ->shouldHaveBeenCalledOnce();
        $revisionA->isApplicable($requestAfterRevisionB)
            ->shouldHaveBeenCalledOnce();
    }

    function it_throws_an_exception_if_revision_returns_same_request_instance(
        Revision $revision,
        Registry $registry,
        RequestInterface $request
    ) {
        // Arrange
        $revision->isApplicable(Argument::any())->willReturn(true);
        $revision->applyToRequest(Argument::any())->willReturnArgument(0);
        $revisionFactory = function () use ($revision): Revision {
            return $revision->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionFactory
        ]);

        // Assert
        $this->shouldThrow(MutationException::class)
            // Act
            ->during('reviseRequest', [$request]);
    }

    function it_throws_an_exception_if_revision_returns_same_response_instance(
        Revision $revision,
        Registry $registry,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        // Arrange
        $revision->isApplicable(Argument::any())->willReturn(true);
        $revision->applyToRequest(Argument::any())->willReturn($request);
        $revision->applyToResponse(Argument::any())->willReturn($response);
        $revisionFactory = function () use ($revision): Revision {
            return $revision->getWrappedObject();
        };

        $registry->retrieveAll()->willReturn([
            $revisionFactory
        ]);

        // Assert
        $this->shouldThrow(MutationException::class)
            // Act
            ->during('reviseResponse', [$request, $response]);
    }

    function it_skips_overridden_revision_while_editing_a_request(
        Registry $registry,
        Revision $revisionA,
        Revision $revisionB,
        RequestInterface $request
    ) {
        // Arrange
        $revisionB->implement(Supersedes::class);
        $revisionB->supersedes(Argument::any())->willReturn(true);
        $revisionB->isApplicable(Argument::any());

        $registry->retrieveAll()->willReturn([
            function () use ($revisionA): Revision {
                return $revisionA->getWrappedObject();
            },
            function () use ($revisionB): Revision {
                return $revisionB->getWrappedObject();
            },
        ]);

        // Act
        $this->reviseRequest($request);

        // Assert
        $revisionA->isApplicable($request)
            ->shouldNotHaveBeenCalled();
        $revisionA->applyToRequest($request)
            ->shouldNotHaveBeenCalled();

        $revisionB->isApplicable($request)
            ->shouldHaveBeenCalled();
    }
}
