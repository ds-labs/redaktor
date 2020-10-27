<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Department;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\MessageEditor;
use DSLabs\Redaktor\Editor\EditorInterface;

final class MessageDepartment implements EditorProvider
{
    /**
     * @inheritDoc
     *
     * @return MessageEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        return new MessageEditor(
            $brief
        );
    }
}