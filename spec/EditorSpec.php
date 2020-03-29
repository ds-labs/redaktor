<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\Brief;
use DSLabs\Redaktor\Editor;
use DSLabs\Redaktor\Exception\MutationException;
use DSLabs\Redaktor\Registry\MessageRevision;
use DSLabs\Redaktor\Registry\RoutingRevision;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see Editor
 */
class EditorSpec extends ObjectBehavior
{
    function it_retrieves_the_same_request_if_the_brief_contains_no_revisions(
        ServerRequestInterface $request
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief($request, [])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($request);
    }

    function it_retrieves_the_same_request_if_the_brief_contains_no_applicable_revisions(
        ServerRequestInterface $request,
        MessageRevision $messageRevision
    ) {
        // Arrange
        $messageRevision->isApplicable($request)->willReturn(false);
        $this->beConstructedWith(
            self::createBrief($request, [$messageRevision])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($request);
    }

    function it_revises_a_request_based_on_applicable_revisions(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequest,
        MessageRevision $messageRevisionA,
        MessageRevision $messageRevisionB
    ) {
        // Arrange
        $messageRevisionA->isApplicable($request)->willReturn(false);

        $messageRevisionB->isApplicable($request)->willReturn(true);
        $messageRevisionB->applyToRequest($request)->willReturn($revisedRequest);

        $brief = self::createBrief($request, [$messageRevisionA, $messageRevisionB]);
        $this->beConstructedWith($brief);

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($revisedRequest);

        $messageRevisionA->applyToRequest(Argument::any())->shouldNotHaveBeenCalled();
        $messageRevisionB->applyToRequest($request)->shouldHaveBeenCalledOnce();
    }

    function it_uses_the_revised_request_to_check_if_the_next_revision_is_applicable_when_revising_the_request(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequestA,
        ServerRequestInterface $revisedRequestB,
        MessageRevision $messageRevisionA,
        MessageRevision $messageRevisionB
    ) {
        // Arrange
        $messageRevisionA->isApplicable($request)->willReturn(true);
        $messageRevisionA->applyToRequest($request)->willReturn($revisedRequestA);

        $messageRevisionB->isApplicable($revisedRequestA)->willReturn(true);
        $messageRevisionB->applyToRequest($revisedRequestA)->willReturn($revisedRequestB);

        $this->beConstructedWith(
            self::createBrief($request, [$messageRevisionA, $messageRevisionB])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($revisedRequestB);
    }

    function it_disallows_unmutated_requests_from_applicable_revisions(
        ServerRequestInterface $request,
        MessageRevision $messageRevision
    ) {
        // Arrange
        $messageRevision->isApplicable($request)->willReturn(true);
        $messageRevision->applyToRequest($request)->willReturn($request);

        $brief = self::createBrief(
            $request,
            [$messageRevision]
        );
        $this->beConstructedWith($brief);

        // Assert
        $this->shouldThrow(MutationException::class)
            // Act
            ->during('reviseRequest');
    }

    function it_retrieves_the_same_response_if_the_brief_contains_no_revisions(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief($request, [])
        );

        // Act
        $this->reviseResponse($response)
            // Assert
            ->shouldBe($response);
    }

    function it_retrieves_the_same_response_if_the_brief_contains_no_applicable_revisions(
        ServerRequestInterface $request,
        MessageRevision $messageRevision,
        ResponseInterface $response
    ) {
        // Arrange
        $messageRevision->isApplicable($request)->willReturn(false);
        $this->beConstructedWith(
            self::createBrief($request, [$messageRevision])
        );

        // Act
        $this->reviseResponse($response)
            // Assert
            ->shouldBe($response);
    }

    function it_revises_a_response_based_on_applicable_revisions(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequest,
        ResponseInterface $response,
        ResponseInterface $revisedResponse,
        MessageRevision $messageRevisionA,
        MessageRevision $messageRevisionB
    ) {
        // Arrange
        $messageRevisionA->isApplicable($request)->willReturn(false);

        $messageRevisionB->isApplicable($request)->willReturn(true);
        $messageRevisionB->applyToRequest(Argument::any())->willReturn($revisedRequest);
        $messageRevisionB->applyToResponse(Argument::any())->willReturn($revisedResponse);

        $brief = self::createBrief($request, [$messageRevisionA, $messageRevisionB]);
        $this->beConstructedWith($brief);

        // Act
        $this->reviseResponse($response)
            // Assert
            ->shouldBe($revisedResponse);
    }

    function it_does_not_double_revise_the_request_if_already_done_so(
        ServerRequestInterface $request,
        MessageRevision $messageRevision,
        ResponseInterface $response
    ) {
        // Arrange
        $messageRevision->isApplicable(Argument::any());

        $this->beConstructedWith(
            self::createBrief(
                $request,
                [$messageRevision]
            )
        );

        // Act
        $this->reviseRequest();
        $this->reviseResponse($response);

        // Assert
        $messageRevision->isApplicable(Argument::any())->shouldHaveBeenCalledOnce();
    }

    function it_disallows_unmutated_responses_from_applicable_revisions(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequest,
        ResponseInterface $response,
        MessageRevision $revision
    ) {
        // Arrange
        $revision->isApplicable(Argument::any())->willReturn(true);
        $revision->applyToRequest(Argument::any())->willReturn($revisedRequest);
        $revision->applyToResponse(Argument::any())->willReturn($response);

        $brief = self::createBrief(
            $request,
            [$revision]
        );
        $this->beConstructedWith($brief);

        // Assert
        $this->shouldThrow(MutationException::class)
            // Act
            ->during('reviseResponse', [$response]);
    }

    function it_revises_routing(
        ServerRequestInterface $request,
        RoutingRevision $routingRevisionA,
        RoutingRevision $routingRevisionB
    ) {
        // Arrange
        $brief = self::createBrief(
            $request,
            [$routingRevisionA, $routingRevisionB]
        );
        $this->beConstructedWith($brief);

        // Act
        $routeConfigurators = $this->reviseRouting();

        // Assert
        $routeConfigurators->shouldIterateAs([
            $routingRevisionA,
            $routingRevisionB
        ]);
    }

    function it_ignores_routing_revisions_while_revising_request(
        ServerRequestInterface $request,
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                $request,
                [$routingRevision]
            )
        );

        // Act
        $this->reviseRequest();

        // Assert
        $routingRevision->__invoke(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_routing_revisions_while_revising_response(
        ServerRequestInterface $request,
        RoutingRevision $routingRevision,
        ResponseInterface $response
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                $request,
                [$routingRevision]
            )
        );

        // Act
        $this->reviseResponse($response);

        // Assert
        $routingRevision->__invoke(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_message_revisions_while_revising_routing(
        ServerRequestInterface $request,
        MessageRevision $messageRevision,
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                $request,
                [$messageRevision, $routingRevision]
            )
        );

        // Act
        $this->reviseRouting()
            // Assert
            ->shouldIterateAs([
                $routingRevision
            ]);
    }

    /**
     * @param RequestInterface|Collaborator $request
     * @param MessageRevision[]|Collaborator[] $revisions
     */
    private static function createBrief(RequestInterface $request, array $revisions): Brief
    {
        $revisions = array_map(static function(Collaborator $revision) {
            return $revision->getWrappedObject();
        }, $revisions);

        return new Brief(
            $request->getWrappedObject(),
            $revisions
        );
    }
}
