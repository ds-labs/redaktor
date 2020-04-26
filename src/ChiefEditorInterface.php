<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Editor\EditorInterface;

interface ChiefEditorInterface
{
    /**
     * Appoint the editor who will carry out the work.
     */
    public function appointEditor(object $request): EditorInterface;
}
