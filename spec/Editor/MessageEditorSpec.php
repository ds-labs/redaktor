<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\MessageEditor;
use DSLabs\Redaktor\Editor\RequestUnaware;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use spec\DSLabs\Redaktor\Double\DummyRequest;
use spec\DSLabs\Redaktor\Double\DummyResponse;

/**
 * @see MessageEditor
 */
class MessageEditorSpec extends ObjectBehavior
{
    function it_retrieves_the_briefed_version()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                $briefedVersion = new Version('foo'),
                []
            )
        );

        // Act
        $this->briefedVersion()
            // Assert
            ->shouldBe($briefedVersion);
    }

    function it_retrieves_the_briefed_revisions(
        Revision $revision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                $briefedRevisions = [
                    $revision
                ]
            )
        );

        // Act
        $this->briefedRevisions()
            // Assert
            ->shouldBe($briefedRevisions);
    }

    function it_retrieves_the_original_request_if_the_brief_contains_no_revisions()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                []
            )
        );

        // Act
        $this->reviseRequest($originalRequest = new DummyRequest())
            // Assert
            ->shouldBe($originalRequest);
    }

    function it_retrieves_the_original_request_if_the_brief_contains_no_applicable_revisions(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $requestRevision->isApplicable($originalRequest = new DummyRequest())->willReturn(false);
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$requestRevision]
            )
        );

        // Act
        $this->reviseRequest($originalRequest)
            // Assert
            ->shouldBe($originalRequest);
    }

    function it_revises_a_request_based_on_applicable_revisions(
        RequestRevision $requestRevisionA,
        RequestRevision $requestRevisionB
    ) {
        // Arrange
        $requestRevisionA->isApplicable($originalRequest = new DummyRequest())->willReturn(false);

        $requestRevisionB->isApplicable($originalRequest)->willReturn(true);
        $requestRevisionB->applyToRequest($originalRequest)->willReturn($revisedRequestB = new DummyRequest());

        $brief = self::createBrief(
            new Version('foo'),
            [$requestRevisionA, $requestRevisionB]
        );
        $this->beConstructedWith($brief);

        // Act
        $revisedRequest = $this->reviseRequest($originalRequest);

        // Assert
        $revisedRequest->shouldBe($revisedRequestB);

        $requestRevisionA->applyToRequest(Argument::any())->shouldNotHaveBeenCalled();
        $requestRevisionB->applyToRequest($originalRequest)->shouldHaveBeenCalledOnce();
    }

    function it_uses_the_revised_request_to_check_if_the_next_revision_is_applicable_when_revising_the_request(
        RequestRevision $requestRevisionA,
        RequestRevision $requestRevisionB
    ) {
        // Arrange
        $requestRevisionA->isApplicable(Argument::any())->willReturn(true);
        $requestRevisionA->applyToRequest(Argument::any())->willReturn($revisedRequestA = new DummyRequest());

        $requestRevisionB->isApplicable(Argument::any())->willReturn(true);
        $requestRevisionB->applyToRequest(Argument::any())->willReturn($revisedRequestB = new DummyRequest());

        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [
                    $requestRevisionA,
                    $requestRevisionB
                ]
            )
        );

        // Act
        $revisedRequest = $this->reviseRequest($originalRequest = new DummyRequest());

        // Assert
        $revisedRequest->shouldBe($revisedRequestB);

        $requestRevisionA->applyToRequest($originalRequest)->shouldHaveBeenCalled();
        $requestRevisionB->applyToRequest($revisedRequestA)->shouldHaveBeenCalled();
    }
    
    function it_throws_an_exception_when_revising_the_response_without_having_previously_revised_the_request()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(new Version('foo'), [])
        );

        // Assert
        $this->shouldThrow(RequestUnaware::class)
            // Act
            ->during('reviseResponse', [new DummyResponse()]);
    }

    function it_retrieves_the_original_response_if_the_brief_contains_no_revisions()
    {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                []
            )
        );
        $this->reviseRequest(new DummyRequest());

        // Act
        $this->reviseResponse($originalResponse = new DummyResponse())
            // Assert
            ->shouldBe($originalResponse);
    }

    function it_retrieves_the_original_response_if_the_brief_contains_no_applicable_revisions(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable(Argument::any())->willReturn(false);
        $this->beConstructedWith(
            self::createBrief(new Version('foo'), [$responseRevision])
        );
        $this->reviseRequest(new DummyRequest());

        // Act
        $this->reviseResponse($originalResponse = new DummyResponse())
            // Assert
            ->shouldBe($originalResponse);
    }

    function it_revises_a_response_based_on_applicable_revisions(
        ResponseRevision $responseRevisionA,
        ResponseRevision $responseRevisionB
    ) {
        // Arrange
        $responseRevisionA->isApplicable(Argument::any())->willReturn(false);

        $responseRevisionB->isApplicable(Argument::any())->willReturn(true);
        $responseRevisionB->applyToResponse(Argument::cetera())->willReturn($revisedResponse = new DummyResponse());

        $brief = self::createBrief(new Version('foo'), [$responseRevisionA, $responseRevisionB]);
        $this->beConstructedWith($brief);

        $this->reviseRequest(new DummyRequest());

        // Act
        $this->reviseResponse($originalResponse = new DummyResponse())
            // Assert
            ->shouldBe($revisedResponse);
    }

    function it_ignores_routing_revisions_while_revising_the_request(
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$routingRevision]
            )
        );

        // Act
        $this->reviseRequest(new DummyRequest());

        // Assert
        $routingRevision->__invoke(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_routing_revisions_while_revising_the_response(
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$routingRevision]
            )
        );
        $this->reviseRequest(new DummyRequest());

        // Act
        $this->reviseResponse(new DummyResponse());

        // Assert
        $routingRevision->__invoke(Argument::any())->shouldNotHaveBeenCalled();
    }

    function it_ignores_applicable_response_revisions_while_revising_the_request(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable(Argument::any())->willReturn(true);

        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$responseRevision]
            )
        );

        // Act
        $this->reviseRequest(new DummyRequest());

        // Assert
        $responseRevision->applyToResponse(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    function it_ignores_applicable_request_revisions_while_revising_the_response(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $requestRevision->isApplicable(Argument::any())->willReturn(true);
        $requestRevision->applyToRequest(Argument::any())->willReturn($revisedRequest = new DummyRequest());

        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$requestRevision]
            )
        );
        $this->reviseRequest(new DummyRequest());

        // Pre-act assertion
        $requestRevision->applyToRequest(Argument::any())
            ->shouldHaveBeenCalled();

        // Act
        $this->reviseResponse(new DummyResponse());

        // Assert
        $requestRevision->applyToRequest(Argument::any())
            ->shouldHaveBeenCalledOnce(); // Called only during `reviseRequest()`
    }

    function it_applies_applicable_request_revisions_to_the_request(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $requestRevision->isApplicable(Argument::any())->willReturn(true);
        $requestRevision->applyToRequest(Argument::any())->willReturn($revisedRequest = new DummyRequest());

        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$requestRevision]
            )
        );

        // Act
        $this->reviseRequest($originalRequest = new DummyRequest());

        // Assert
        $requestRevision->applyToRequest($originalRequest)->shouldHaveBeenCalled();
    }

    function it_applies_applicable_response_revisions_to_the_response(
        ResponseRevision $responseRevision
    ) {
        // Arrange
        $responseRevision->isApplicable(Argument::any())->willReturn(true);
        $responseRevision->applyToResponse(Argument::cetera())->willReturn($revisedResponse = new DummyResponse());

        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [$responseRevision]
            )
        );
        $this->reviseRequest($originalRequest = new DummyRequest());

        // Act
        $this->reviseResponse($originalResponse = new DummyResponse());

        // Assert
        $responseRevision->applyToResponse($originalRequest, $originalResponse)->shouldHaveBeenCalled();
    }

    /**
     * Each `ResponseRevision` will receive an instance of the response (revised up to this point) and the request
     * corresponding to the revision.
     */
    function it_passes_the_corresponding_revised_request_in_when_revising_the_response(
        ResponseRevision $responseRevisionA,
        RequestRevision $requestRevision,
        ResponseRevision $responseRevisionB
    ) {
        // Arrange
        $responseRevisionA->isApplicable(Argument::any())->willReturn(true);
        $responseRevisionA->applyToResponse(Argument::cetera())->willReturn(new DummyResponse());

        $requestRevision->isApplicable(Argument::any())->willReturn(true);
        $requestRevision->applyToRequest(Argument::any())->willReturn($revisedRequest = new DummyRequest());

        $responseRevisionB->isApplicable(Argument::any())->willReturn(true);
        $responseRevisionB->applyToResponse(Argument::cetera())->willReturn(new DummyResponse());

        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
                [
                    $responseRevisionA,
                    $requestRevision,
                    $responseRevisionB,
                ]
            )
        );
        $this->reviseRequest($originalRequest = new DummyRequest());

        // Act
        $this->reviseResponse(new DummyResponse());

        // Assert
        $responseRevisionA->applyToResponse($originalRequest, Argument::any())->shouldHaveBeenCalled();
        $responseRevisionB->applyToResponse($revisedRequest, Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * @param Version $version
     * @param Revision[]|Collaborator[] $revisions
     */
    private static function createBrief(Version $version, array $revisions): Brief
    {
        $revisions = array_map(static function(Collaborator $revision) {
            return $revision->getWrappedObject();
        }, $revisions);

        return new Brief(
            $version,
            $revisions
        );
    }
}
