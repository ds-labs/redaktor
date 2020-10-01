<?php

namespace DSLabs\Redaktor\Editor;

interface EditorInterface
{
    /**
     * Retrieves the request passed on in the briefing.
     */
    public function retrieveBriefedRequest(): object;

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
     * Passes the briefed request through the applicable revisions and returns
     * the revised request.
     */
    public function reviseRequest(): object;

    /**
     * Passes the response through the applicable revisions and returns
     * the revised response.
     */
    public function reviseResponse(object $response): object;
}