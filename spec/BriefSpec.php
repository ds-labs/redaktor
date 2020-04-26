<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\Brief;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use PhpSpec\ObjectBehavior;
use spec\DSLabs\Redaktor\Double\DummyRequest;

/**
 * @see Brief
 */
class BriefSpec extends ObjectBehavior
{
    function it_disallows_non_revision_instances()
    {
        // Arrange
        $this->beConstructedWith(
            $request = new DummyRequest(),
            [
                'foo',
            ]
        );

        // Assert
        $this->shouldThrow(\InvalidArgumentException::class)
            // Act
            ->duringInstantiation();
    }

    function it_retrieves_original_request()
    {
        // Arrange
        $this->beConstructedWith(
            $request = new DummyRequest(),
            []
        );

        // Act
        $this->request()
            // Assert
            ->shouldBe($request);
    }

    function it_retrieves_list_of_revisions(
        RequestRevision $requestRevision,
        ResponseRevision $responseRevision,
        RoutingRevision $routingRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            $request = new DummyRequest(),
            [
                $requestRevision,
                $responseRevision,
                $routingRevision,
            ]
        );

        // Act
        $this->revisions()
            // Assert
            ->shouldBe([
                $requestRevision,
                $responseRevision,
                $routingRevision,
            ]);
    }
}