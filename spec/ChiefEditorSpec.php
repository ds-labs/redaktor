<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor;

use DSLabs\Redaktor\Brief;
use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\Editor;
use DSLabs\Redaktor\Registry\MessageRevision;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\Supersedes;
use DSLabs\Redaktor\Version\VersionResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use spec\DSLabs\Redaktor\Double\Revision\DummyMessageRevision;

/**
 * @see ChiefEditor
 */
class ChiefEditorSpec extends ObjectBehavior
{
    function let(
        Registry $registry,
        VersionResolver $versionResolver
    ) {
        $this->beConstructedWith($registry, $versionResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChiefEditor::class);
    }

    function it_appoints_editor_for_a_request_with_no_defined_version(
        Registry $registry,
        VersionResolver $versionResolver,
        ServerRequestInterface $request
    ) {
        // Arrange
        $registry->retrieveAll()->willReturn([]);

        // Act
        $editor = $this->appointEditor($request);

        // Assert
        $versionResolver->resolve($request)->shouldHaveBeenCalled();
        $registry->retrieveAll()->shouldHaveBeenCalled();

        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request->getWrappedObject(),
                    []
                )
            )
        );
    }

    function it_appoints_editor_for_a_request_with_a_defined_version(
        Registry $registry,
        VersionResolver $versionResolver,
        ServerRequestInterface $request
    ) {
        // Arrange
        $versionResolver->resolve(Argument::any())->willReturn('foo');
        $registry->retrieveSince('foo')->willReturn([]);

        // Act
        $editor = $this->appointEditor($request);

        // Assert
        $versionResolver->resolve($request)->shouldHaveBeenCalled();
        $registry->retrieveSince('foo')->shouldHaveBeenCalled();

        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request->getWrappedObject(),
                    []
                )
            )
        );
    }

    function it_discards_superseded_revisions(
        Registry $registry,
        ServerRequestInterface $request,
        MessageRevision $supersededRevision,
        MessageRevision $supersederRevision
    ) {
        // Arrange
        $supersederRevision->implement(Supersedes::class);
        $supersederRevision->supersedes($supersededRevision)->willReturn(true);

        $registry->retrieveAll()->willReturn([
            static function() use ($supersededRevision) {
                return $supersededRevision->getWrappedObject();
            },
            static function() use ($supersederRevision) {
                return $supersederRevision->getWrappedObject();
            },
        ]);

        // Act
        $editor = $this->appointEditor($request);

        // Assert
        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request->getWrappedObject(),
                    [
                        $supersederRevision->getWrappedObject(),
                    ]
                )
            )
        );
    }

    function it_resolves_closure_revision_definition(
        VersionResolver $versionResolver,
        Registry $registry,
        MessageRevision $revisionA,
        ServerRequestInterface $request
    ) {
        // Arrange
        $versionResolver->resolve(Argument::any())->willReturn(null);
        $registry->retrieveAll()->willReturn([
            static function () use ($revisionA) {
                return $revisionA->getWrappedObject();
            }
        ]);

        // Act
        $editor = $this->appointEditor($request);

        // Assert
        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request->getWrappedObject(),
                    [
                        $revisionA->getWrappedObject(),
                    ]
                )
            )
        );
    }

    function it_resolves_class_name_revision_definition(
        VersionResolver $versionResolver,
        Registry $registry,
        ServerRequestInterface $request
    ) {
        // Arrange
        $versionResolver->resolve(Argument::any())->willReturn(null);
        $registry->retrieveAll()->willReturn([
            DummyMessageRevision::class
        ]);

        // Act
        $editor = $this->appointEditor($request);

        // Assert
        $editor->shouldBeLike(
            new Editor(
                new Brief(
                    $request->getWrappedObject(),
                    [
                        new DummyMessageRevision(),
                    ]
                )
            )
        );
    }
}
