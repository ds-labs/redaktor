<?php

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\MessageEditorInterface;
use DSLabs\Redaktor\Editor\RoutingEditorInterface;
use DSLabs\Redaktor\Version\Version;

interface ChiefEditorInterface
{
    /**
     * Get in touch with an editor provider.
     */
    public function speakTo(EditorProvider $editorProvider): self;

    /**
     * Appoint the editor who will carry out the work.
     *
     * @return RoutingEditorInterface|MessageEditorInterface
     */
    public function appointEditor(Version $version): EditorInterface;
}
