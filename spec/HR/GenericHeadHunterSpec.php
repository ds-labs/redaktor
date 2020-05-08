<?php

namespace spec\DSLabs\Redaktor\HR;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\HR\GenericHeadHunter;
use DSLabs\Redaktor\Revision\Revision;
use PhpSpec\ObjectBehavior;

/**
 * @see GenericHeadHunter
 */
class GenericHeadHunterSpec extends ObjectBehavior
{
    function it_hires_a_generic_editor(
        \stdClass $request,
        Revision $revision
    ) {
        // Arrange
        $brief = new Brief(
            $request->getWrappedObject(),
            $revisions = [
                $revision->getWrappedObject()
            ]
        );

        // Act
        $editor = $this->hireEditor($brief);

        // Assert
        $editor->shouldBeAnInstanceOf(Editor::class);
        $editor->getBriefedRequest()->shouldBe($request);
        $editor->getBriefedRevisions()->shouldBe($revisions);
    }
}
