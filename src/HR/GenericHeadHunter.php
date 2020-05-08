<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\HR;

use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\Editor;
use DSLabs\Redaktor\Editor\EditorInterface;

final class GenericHeadHunter implements HeadHunter
{
    /**
     * @inheritDoc
     */
    public function hireEditor(Brief $brief): EditorInterface
    {
        return new Editor(
            $brief
        );
    }
}