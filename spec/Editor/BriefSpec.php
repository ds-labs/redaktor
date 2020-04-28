<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Revision\Revision;
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
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $this->beConstructedWith(
            $request = new DummyRequest(),
            [
                $revisionA,
                $revisionB,
            ]
        );

        // Act
        $this->revisions()
            // Assert
            ->shouldBe([
                $revisionA,
                $revisionB,
            ]);
    }
}