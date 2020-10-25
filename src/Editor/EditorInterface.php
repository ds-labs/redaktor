<?php

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Version\Version;

interface EditorInterface
{
    /**
     * Retrieves the version the Editor was briefed on.
     */
    public function briefedVersion(): Version;

    /**
     * Retrieves the list of revisions passed on in the briefing.
     */
    public function retrieveBriefedRevisions(): array;

    /**
     * Passes the routes through every routing revision and returns the
     * revised routes.
     */
    public function reviseRouting(iterable $routes): iterable;

    /**
     * Passes the request through the applicable revisions and returns
     * the revised request.
     */
    public function reviseRequest(object $request): object;

    /**
     * Passes the response through the applicable revisions and returns
     * the revised response.
     */
    public function reviseResponse(object $response): object;
}