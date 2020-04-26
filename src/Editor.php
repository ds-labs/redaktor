<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Exception\MutationException;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\RoutingRevision;

/**
 * Given a Brief is able to revise the application routes, Request and/or Response.
 */
final class Editor implements EditorInterface
{
    /**
     * @var Brief
     */
    private $brief;

    /**
     * @var bool
     */
    private $requestIsRevised = false;

    /**
     * @var RequestRevision[]|ResponseRevision[]
     */
    private $applicableRevisions = [];

    public function __construct(
        Brief $brief
    ) {
        $this->brief = $brief;
    }

    /**
     * Loops through the revisions passing in the list of routes to
     * routing revisions, giving them the chance to amend them.
     */
    public function reviseRouting(iterable $routes): iterable
    {
        foreach ($this->brief->revisions() as $revision) {
            if ($revision instanceof RoutingRevision) {
                $routes = $revision($routes);
            }
        }

        return $routes;
    }

    /**
     * Revise the Request given in the Brief.
     */
    public function reviseRequest(): object
    {
        $revisions = array_filter($this->brief->revisions(), static function($revision): bool {
            return $revision instanceof RequestRevision
                || $revision instanceof ResponseRevision;
        });

        $upToDateRequest = array_reduce(
            $revisions,
            function(
                object $request,
                $revision
            ) {
                /** @var RequestRevision|ResponseRevision $revision */
                if (!$revision->isApplicable($request)) {
                    return $request;
                }

                $this->applicableRevisions[] = $revision;

                if (!$revision instanceof RequestRevision) {
                    return $request;
                }

                $revisedRequest = $revision->applyToRequest($request);
                if ($revisedRequest === $request) {
                    throw MutationException::inRevision($revision);
                }

                return $revisedRequest;
            },
            $this->brief->request()
        );

        $this->markRequestAsRevised();

        return $upToDateRequest;
    }

    /**
     * Revise the given Response based on the applicable revisions for
     * the Request specified in the Brief.
     */
    public function reviseResponse(object $response): object
    {
        if (!$this->requestIsRevised) {
            $this->reviseRequest();
        }

        return array_reduce(
            array_reverse($this->applicableRevisions),
            static function($response, $revision): object {

                if (!$revision instanceof ResponseRevision) {
                    return $response;
                }

                $revisedResponse = $revision->applyToResponse($response);

                if ($revisedResponse === $response) {
                    throw MutationException::inRevision($revision);
                }

                return $revisedResponse;
            },
            $response
        );
    }

    /**
     * Set an internal property indicating that the Request has already been
     * revised, and therefore the applicable revisions are known by the Editor.
     */
    private function markRequestAsRevised(): void
    {
        $this->requestIsRevised = true;
    }
}
