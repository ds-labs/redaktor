<?php

namespace DSLabs\Redaktor\HR;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;

interface HeadHunter
{
    /**
     * Hire the editor that will carry out the work.
     */
    public function hireEditor(Brief $brief): EditorInterface;
}