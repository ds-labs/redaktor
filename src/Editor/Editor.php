<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Revision\RoutingRevision;

/**
 * Given a Brief is able to revise the application routes, request and/or response.
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
     * @var ResponseRevision[]
     */
    private $applicableResponseRevisions = [];

    public function __construct(
        Brief $brief
    ) {
        $this->brief = $brief;
    }

    /**
     * @inheritDoc
     */
    public function retrieveBriefedRequest(): object
    {
        return $this->brief->request();
    }

    /**
     * @inheritDoc
     *
     * @return Revision[]
     */
    public function retrieveBriefedRevisions(): array
    {
        return $this->brief->revisions();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function reviseRequest(): object
    {
        $revisions = array_filter($this->brief->revisions(), static function($revision): bool {
            return $revision instanceof RequestRevision
                || $revision instanceof ResponseRevision;
        });

        $upToDateRequest = array_reduce(
            $revisions,
            function(object $request, object $revision): object {
                /** @var RequestRevision|ResponseRevision $revision */
                if (!$revision->isApplicable($request)) {
                    return $request;
                }

                if ($revision instanceof ResponseRevision) {
                    $this->applicableResponseRevisions[] = $revision;

                    return $request;
                }

                return $revision->applyToRequest($request);
            },
            $this->brief->request()
        );

        $this->markRequestAsRevised();

        return $upToDateRequest;
    }

    /**
     * @inheritDoc
     */
    public function reviseResponse(object $response): object
    {
        if (!$this->requestIsRevised) {
            $this->reviseRequest();
        }

        return array_reduce(
            array_reverse($this->applicableResponseRevisions),
            static function($response, $revision): object {
                return $revision->applyToResponse($response);
            },
            $response
        );
    }

    /**
     * Set an internal flag indicating that the Request has already been
     * revised, and therefore the applicable revisions are known by the Editor.
     */
    private function markRequestAsRevised(): void
    {
        $this->requestIsRevised = true;
    }
}
