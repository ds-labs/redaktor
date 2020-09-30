<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\Editor\MutationException;
use DSLabs\Redaktor\Revision\MessageRevision;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use spec\DSLabs\Redaktor\Double\DummyRequest;
use spec\DSLabs\Redaktor\Double\DummyResponse;

/**
 * @see Editor
 */
class EditorSpec extends ObjectBehavior
{
    function it_retrieves_the_briefed_request()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                $briefedRequest = new DummyRequest(),
                []
            )
        );

        // Act
        $request = $this->retrieveBriefedRequest();

        // Assert
        $request->shouldBe($briefedRequest);
    }

    function it_retrieves_the_briefed_revisions(
        Revision $revision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                $briefedRevisions = [
                    $revision
                ]
            )
        );

        // Act
        $revisions = $this->retrieveBriefedRevisions();

        // Assert
        $revisions->shouldBe($briefedRevisions);
    }

    function it_retrieves_the_same_request_if_the_brief_contains_no_revisions()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                $request = new DummyRequest(),
                []
            )
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($request);
    }

    function it_retrieves_the_same_request_if_the_brief_contains_no_applicable_revisions(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $requestRevision->isApplicable($request = new DummyRequest())->willReturn(false);
        $this->beConstructedWith(
            self::createBrief($request, [$requestRevision])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($request);
    }

    function it_revises_a_request_based_on_applicable_revisions(
        RequestRevision $requestRevisionA,
        RequestRevision $requestRevisionB
    ) {
        // Arrange
        $request = new DummyRequest();
        $revisedRequest = new DummyRequest();
        $requestRevisionA->isApplicable($request)->willReturn(false);

        $requestRevisionB->isApplicable($request)->willReturn(true);
        $requestRevisionB->applyToRequest($request)->willReturn($revisedRequest);

        $brief = self::createBrief($request, [$requestRevisionA, $requestRevisionB]);
        $this->beConstructedWith($brief);

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($revisedRequest);

        $requestRevisionA->applyToRequest(Argument::any())->shouldNotHaveBeenCalled();
        $requestRevisionB->applyToRequest($request)->shouldHaveBeenCalledOnce();
    }

    function it_uses_the_revised_request_to_check_if_the_next_revision_is_applicable_when_revising_the_request(
        RequestRevision $requestRevisionA,
        RequestRevision $requestRevisionB
    ) {
        // Arrange
        $request = new DummyRequest();
        $revisedRequestA = new DummyRequest();
        $revisedRequestB = new DummyRequest();
        $requestRevisionA->isApplicable($request)->willReturn(true);
        $requestRevisionA->applyToRequest($request)->willReturn($revisedRequestA);

        $requestRevisionB->isApplicable($revisedRequestA)->willReturn(true);
        $requestRevisionB->applyToRequest($revisedRequestA)->willReturn($revisedRequestB);

        $this->beConstructedWith(
            self::createBrief($request, [$requestRevisionA, $requestRevisionB])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($revisedRequestB);
    }

    function it_retrieves_the_same_response_if_the_brief_contains_no_revisions()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                []
            )
        );

        // Act
        $this->reviseResponse($response = new DummyResponse())
            // Assert
            ->shouldBe($response);
    }

    function it_retrieves_the_same_response_if_the_brief_contains_no_applicable_revisions(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable($request = new DummyRequest())->willReturn(false);
        $this->beConstructedWith(
            self::createBrief($request, [$responseRevision])
        );

        // Act
        $this->reviseResponse($response = new DummyResponse())
            // Assert
            ->shouldBe($response);
    }

    function it_revises_a_response_based_on_applicable_revisions(
        ResponseRevision $responseRevisionA,
        ResponseRevision $responseRevisionB
    ) {
        // Arrange
        $responseRevisionA->isApplicable(Argument::any())->willReturn(false);

        $responseRevisionB->isApplicable(Argument::any())->willReturn(true);
        $responseRevisionB->applyToResponse(Argument::any())->willReturn($revisedResponse = new DummyResponse());

        $brief = self::createBrief(new DummyRequest(), [$responseRevisionA, $responseRevisionB]);
        $this->beConstructedWith($brief);

        // Act
        $this->reviseResponse(new DummyResponse())
            // Assert
            ->shouldBe($revisedResponse);
    }

    function it_does_not_double_revise_the_request_if_already_done_so(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable(Argument::any());

        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                [$responseRevision]
            )
        );

        // Act
        $this->reviseRequest();
        $this->reviseResponse(new DummyResponse());

        // Assert
        $responseRevision->isApplicable(Argument::any())->shouldHaveBeenCalledOnce();
    }

    function it_passes_the_routes_iterable_through_all_routing_revisions(
        RoutingRevision $routingRevisionA,
        RoutingRevision $routingRevisionB
    ) {
        // Arrange
        $brief = self::createBrief(
            new DummyRequest(),
            [$routingRevisionA, $routingRevisionB]
        );
        $this->beConstructedWith($brief);
        $routingRevisionA->__invoke(Argument::any())->willReturn($routesAfterRevisionA = ['foo']);
        $routingRevisionB->__invoke(Argument::any())->willReturn($routesAfterRevisionB = ['foo', 'bar']);

        // Act
        $revisedRoutes = $this->reviseRouting($originalRoutes = []);

        // Assert
        $routingRevisionA->__invoke($originalRoutes)->shouldHaveBeenCalled();
        $routingRevisionB->__invoke($routesAfterRevisionA)->shouldHaveBeenCalled();

        $revisedRoutes->shouldBe($routesAfterRevisionB);
    }

    function it_ignores_routing_revisions_while_revising_the_request(
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                [$routingRevision]
            )
        );

        // Act
        $this->reviseRequest();

        // Assert
        $routingRevision->__invoke(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_routing_revisions_while_revising_the_response(
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                [$routingRevision]
            )
        );

        // Act
        $this->reviseResponse(new DummyResponse());

        // Assert
        $routingRevision->__invoke(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_request_revisions_while_revising_the_routing(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                [$requestRevision]
            )
        );

        // Act
        $this->reviseRouting([]);

        // Assert
        $requestRevision->isApplicable(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_response_revisions_while_revising_the_routing(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                [$responseRevision]
            )
        );

        // Act
        $this->reviseRouting([]);

        // Assert
        $responseRevision->isApplicable(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_applicable_response_revisions_while_revising_the_request(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable(Argument::any())->willReturn(true);

        $this->beConstructedWith(
            self::createBrief(
                new DummyRequest(),
                [$responseRevision]
            )
        );

        // Act
        $this->reviseRequest();

        // Assert
        $responseRevision->applyToResponse(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_applicable_request_revisions_while_revising_the_response(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $requestRevision->isApplicable(Argument::any())->willReturn(true);
        $requestRevision->applyToRequest(Argument::any())->willReturn($revisedRequest = new DummyRequest());

        $this->beConstructedWith(
            self::createBrief(
                $originalRequest = new DummyRequest(),
                [$requestRevision]
            )
        );

        // Act
        $this->shouldNotThrow(\Throwable::class)
            // Assert
            ->during('reviseResponse', [new DummyResponse()]);
    }

    function it_applies_applicable_request_revisions_to_the_request(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $requestRevision->isApplicable(Argument::any())->willReturn(true);
        $requestRevision->applyToRequest(Argument::any())->willReturn($revisedRequest = new DummyRequest());

        $this->beConstructedWith(
            self::createBrief(
                $originalRequest = new DummyRequest(),
                [$requestRevision]
            )
        );

        // Act
        $this->reviseRequest();

        // Assert
        $requestRevision->applyToRequest($originalRequest)->shouldHaveBeenCalled();
    }

    function it_applies_applicable_response_revisions_to_the_response(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable(Argument::any())->willReturn(true);
        $responseRevision->applyToResponse(Argument::any())->willReturn($revisedResponse = new DummyResponse());

        $this->beConstructedWith(
            self::createBrief(
                $originalRequest = new DummyRequest(),
                [$responseRevision]
            )
        );

        // Act
        $this->reviseResponse($originalResponse = new DummyResponse());

        // Assert
        $responseRevision->applyToResponse($originalResponse)->shouldHaveBeenCalled();
    }

    /**
     * @param object $request
     * @param Revision[]|Collaborator[] $revisions
     */
    private static function createBrief(object $request, array $revisions): Brief
    {
        $revisions = array_map(static function(Collaborator $revision) {
            return $revision->getWrappedObject();
        }, $revisions);

        return new Brief(
            $request,
            $revisions
        );
    }
}
