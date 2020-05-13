<?php

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\HumanResources\HumanResources;

interface ChiefEditorInterface
{
    /**
     * Get in touch with Human Resources to hire an editor.
     */
    public function speakTo(HumanResources $humanResources): self;

    /**
     * Appoint the editor who will carry out the work.
     */
    public function appointEditor(object $request): EditorInterface;
}
