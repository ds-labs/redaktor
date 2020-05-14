<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\Editor\EditorInterface;

final class EditorDepartment implements EditorProvider
{
    /**
     * @inheritDoc
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        return new Editor(
            $brief
        );
    }
}