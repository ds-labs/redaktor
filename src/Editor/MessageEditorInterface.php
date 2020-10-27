<?php

namespace DSLabs\Redaktor\Editor;

interface MessageEditorInterface extends EditorInterface
{
    /**
     * Revise the given $request to the briefed version.
     */
    public function reviseRequest(object $request): object;

    /**
     * Revise the given $response to the briefed version.
     */
    public function reviseResponse(object $response): object;
}