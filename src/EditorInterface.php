<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

interface EditorInterface
{
    /**
     * Creates a Generator containing all routing revisions.
     */
    public function reviseRouting(iterable $routes): iterable;

    /**
     * Passes the request hold through the applicable revisions and returns
     * the revised request.
     */
    public function reviseRequest(): object;

    /**
     * Passes the response through the applicable revisions and returns
     * the revised response.
     */
    public function reviseResponse(object $response): object;
}