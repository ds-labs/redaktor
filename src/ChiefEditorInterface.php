<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

interface ChiefEditorInterface
{
    /**
     * Appoint the editor who will carry out the work.
     */
    public function appointEditor(object $request): EditorInterface;
}
