<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\RoutingEditor;

final class RoutingDepartment implements EditorProvider
{
    /**
     * @inheritDoc
     *
     * @return RoutingEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        return new RoutingEditor(
            $brief
        );
    }
}