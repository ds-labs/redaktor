<?php

namespace spec\DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\RoutingEditor;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;

/**
 * @see GenericRoutingDepartment
 */
class GenericRoutingDepartmentSpec extends ObjectBehavior
{
    function it_provides_a_routing_editor(
        Revision $revision
    ) {
        // Arrange
        $brief = new Brief(
            $version = new Version('foo'),
            $revisions = [
                $revision->getWrappedObject(),
            ]
        );

        // Act
        $editor = $this->provideEditor($brief);

        // Assert
        $editor->shouldBeAnInstanceOf(RoutingEditor::class);
        $editor->briefedVersion()->shouldBe($version);
        $editor->briefedRevisions()->shouldBe($revisions);
    }
}
