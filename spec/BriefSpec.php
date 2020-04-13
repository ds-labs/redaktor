<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\Brief;
use DSLabs\Redaktor\Registry\MessageRevision;
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
        MessageRevision $messageRevisionA,
        MessageRevision $messageRevisionB
    ) {
        // Arrange
        $this->beConstructedWith(
            $request = new DummyRequest(),
            [
                $messageRevisionA,
                $messageRevisionB
            ]
        );

        // Act
        $this->revisions()
            // Assert
            ->shouldBe([
                $messageRevisionA,
                $messageRevisionB
            ]);
    }
}