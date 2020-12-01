<?php

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditor;
use DSLabs\Redaktor\Editor\RoutingEditor;
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
     * @return RoutingEditor|MessageEditor
     */
    public function appointEditor(Version $version): EditorInterface;
}
