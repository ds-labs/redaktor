<?php

namespace DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\RoutingEditorInterface;

interface RoutingDepartment extends EditorProvider
{
    /**
     * @param Brief $brief
     *
     * @return RoutingEditorInterface
     */
    public function provideEditor(Brief $brief): EditorInterface;
}
