<?php

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\HR\HeadHunter;

interface ChiefEditorInterface
{
    /**
     * Speaks to the HeadHunter that will be in charge of hiring.
     */
    public function speakTo(HeadHunter $headHunter): self;

    /**
     * Appoint the editor who will carry out the work.
     */
    public function appointEditor(object $request): EditorInterface;
}
