<?php

namespace DSLabs\Redaktor\HumanResources;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;

interface HumanResources
{
    /**
     * Hire the editor that will carry out the work.
     */
    public function hireEditor(Brief $brief): EditorInterface;
}