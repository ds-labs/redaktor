<?php

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;

interface EditorInterface
{
    /**
     * Retrieves the version that was briefed on.
     */
    public function briefedVersion(): Version;

    /**
     * Retrieves the list of revisions that was briefed on.
     *
     * @return Revision[]
     */
    public function briefedRevisions(): array;

    /**
     * Revise the given $routes to the briefed version.
     */
    public function reviseRouting(iterable $routes): iterable;

    /**
     * Revise the given $request to the briefed version.
     */
    public function reviseRequest(object $request): object;

    /**
     * Revise the given $response to the briefed version.
     */
    public function reviseResponse(object $response): object;
}