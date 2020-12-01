<?php

namespace spec\DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\MessageEditor;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;

/**
 * @see MessageDepartment
 */
class MessageDepartmentSpec extends ObjectBehavior
{
    function it_provides_a_message_editor(
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
        $editor->shouldBeAnInstanceOf(MessageEditor::class);
        $editor->briefedVersion()->shouldBe($version);
        $editor->briefedRevisions()->shouldBe($revisions);
    }
}
