<?php

namespace spec\DSLabs\Redaktor\HumanResources;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\Revision\Revision;
use PhpSpec\ObjectBehavior;

/**
 * @see HumanResourcesDepartment
 */
class HumanResourcesDepartmentSpec extends ObjectBehavior
{
    function it_provides_a_generic_editor(
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
        $editor = $this->provideEditor($brief);

        // Assert
        $editor->shouldBeAnInstanceOf(Editor::class);
        $editor->retrieveBriefedRequest()->shouldBe($request);
        $editor->retrieveBriefedRevisions()->shouldBe($revisions);
    }
}
