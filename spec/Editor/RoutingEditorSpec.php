<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\RoutingEditor;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;

/**
 * @see RoutingEditor
 */
class RoutingEditorSpec extends ObjectBehavior
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
                    $revision,
                ]
            )
        );

        // Act
        $this->briefedRevisions()
            // Assert
            ->shouldBe($briefedRevisions);
    }

    function it_passes_the_routes_through_all_routing_revisions(
        RoutingRevision $routingRevisionA,
        RoutingRevision $routingRevisionB
    ) {
        // Arrange
        $brief = self::createBrief(
            new Version('foo'),
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

    function it_ignores_request_revisions_while_revising_the_routing(
        RequestRevision $requestRevision
    ) {
        // Arrange
        $this->beConstructedWith(
            self::createBrief(
                new Version('foo'),
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
                new Version('foo'),
                [$responseRevision]
            )
        );

        // Act
        $this->reviseRouting([]);

        // Assert
        $responseRevision->isApplicable(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @param Version $version
     * @param Revision[]|Collaborator[] $revisions
     */
    private static function createBrief(Version $version, array $revisions): Brief
    {
        $revisions = array_map(static function (Collaborator $revision) {
            return $revision->getWrappedObject();
        }, $revisions);

        return new Brief(
            $version,
            $revisions
        );
    }
}
