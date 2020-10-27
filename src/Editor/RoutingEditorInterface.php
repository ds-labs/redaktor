<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

interface RoutingEditorInterface extends EditorInterface
{
    /**
     * Revise the given $routes to the briefed version.
     */
    public function reviseRouting(iterable $routes): iterable;
}
