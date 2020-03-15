<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\Brief;
use DSLabs\Redaktor\Registry\Revision;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see Brief
 */
class BriefSpec extends ObjectBehavior
{
    function it_disallows_non_revision_instances(
        ServerRequestInterface $request
    ) {
        // Arrange
        $this->beConstructedWith(
            $request,
            [
                'foo',
            ]
        );

        // Assert
        $this->shouldThrow(\InvalidArgumentException::class)
            // Act
            ->duringInstantiation();
    }
    
    function it_retrieves_original_request(
        ServerRequestInterface $request
    ) {
        // Arrange
        $this->beConstructedWith($request, []);
    
        // Act
        $this->request()
            // Assert
            ->shouldBe($request);
    }

    function it_retrieves_list_of_revisions(
        ServerRequestInterface $request,
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $this->beConstructedWith(
            $request,
            [
                $revisionA,
                $revisionB
            ]
        );

        // Act
        $this->revisions()
            // Assert
            ->shouldBe([
                $revisionA,
                $revisionB
            ]);
    }
}