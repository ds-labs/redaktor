<?php

namespace DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;

interface EditorProvider
{
    /**
     * Provide the editor that will carry out the work.
     */
    public function provideEditor(Brief $brief): EditorInterface;
}
