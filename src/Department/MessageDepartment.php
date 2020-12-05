<?php

namespace DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditorInterface;

interface MessageDepartment extends EditorProvider
{
    /**
     * @param Brief $brief
     *
     * @return MessageEditorInterface
     */
    public function provideEditor(Brief $brief): EditorInterface;
}
