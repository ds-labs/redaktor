<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\Brief;
use DSLabs\Redaktor\Editor;
use DSLabs\Redaktor\Exception\MutationException;
use DSLabs\Redaktor\Registry\Revision;
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
        Revision $revision
    ) {
        // Arrange
        $revision->isApplicable($request)->willReturn(false);
        $this->beConstructedWith(
            self::createBrief($request, [$revision])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($request);
    }

    function it_revises_a_request_based_on_applicable_revisions(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequest,
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $revisionA->isApplicable($request)->willReturn(false);

        $revisionB->isApplicable($request)->willReturn(true);
        $revisionB->applyToRequest($request)->willReturn($revisedRequest);

        $brief = self::createBrief($request, [$revisionA, $revisionB]);
        $this->beConstructedWith($brief);

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($revisedRequest);

        $revisionA->applyToRequest(Argument::any())->shouldNotHaveBeenCalled();
        $revisionB->applyToRequest($request)->shouldHaveBeenCalledOnce();
    }

    function it_uses_the_revised_request_to_check_if_the_next_revision_is_applicable_when_revising_the_request(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequestA,
        ServerRequestInterface $revisedRequestB,
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $revisionA->isApplicable($request)->willReturn(true);
        $revisionA->applyToRequest($request)->willReturn($revisedRequestA);

        $revisionB->isApplicable($revisedRequestA)->willReturn(true);
        $revisionB->applyToRequest($revisedRequestA)->willReturn($revisedRequestB);

        $this->beConstructedWith(
            self::createBrief($request, [$revisionA, $revisionB])
        );

        // Act
        $this->reviseRequest()
            // Assert
            ->shouldBe($revisedRequestB);
    }

    function it_disallows_unmutated_requests_from_applicable_revisions(
        ServerRequestInterface $request,
        Revision $revision
    ) {
        // Arrange
        $revision->isApplicable($request)->willReturn(true);
        $revision->applyToRequest($request)->willReturn($request);

        $brief = self::createBrief(
            $request,
            [$revision]
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
        Revision $revision,
        ResponseInterface $response
    ) {
        // Arrange
        $revision->isApplicable($request)->willReturn(false);
        $this->beConstructedWith(
            self::createBrief($request, [$revision])
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
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $revisionA->isApplicable($request)->willReturn(false);

        $revisionB->isApplicable($request)->willReturn(true);
        $revisionB->applyToRequest(Argument::any())->willReturn($revisedRequest);
        $revisionB->applyToResponse(Argument::any())->willReturn($revisedResponse);

        $brief = self::createBrief($request, [$revisionA, $revisionB]);
        $this->beConstructedWith($brief);

        // Act
        $this->reviseResponse($response)
            // Assert
            ->shouldBe($revisedResponse);
    }

    function it_disallows_unmutated_responses_from_applicable_revisions(
        ServerRequestInterface $request,
        ServerRequestInterface $revisedRequest,
        ResponseInterface $response,
        Revision $revision
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

    /**
     * @param RequestInterface|Collaborator $request
     * @param Revision[]|Collaborator[] $revisions
     */
    private static function createBrief(RequestInterface $request, array $revisions): Brief
    {
        $wrappedRevisions = array_map(static function (Collaborator $revision) {
            return $revision->getWrappedObject();
        }, $revisions);

        return new Brief(
            $request->getWrappedObject(),
            $wrappedRevisions
        );
    }
}
