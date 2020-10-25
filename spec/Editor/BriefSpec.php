<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
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
            new Version('foo'),
            [
                'foo',
            ]
        );

        // Assert
        $this->shouldThrow(\InvalidArgumentException::class)
            // Act
            ->duringInstantiation();
    }

    function it_retrieves_version()
    {
        // Arrange
        $this->beConstructedWith(
            $version = new Version('foo'),
            []
        );

        // Act
        $this->version()
            // Assert
            ->shouldBe($version);
    }

    function it_retrieves_list_of_revisions(
        Revision $revisionA,
        Revision $revisionB
    ) {
        // Arrange
        $this->beConstructedWith(
            new Version('foo'),
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