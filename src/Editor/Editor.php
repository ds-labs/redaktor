<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use DSLabs\Redaktor\Version\Version;

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
     * @var array
     */
    private $notes = [];

    public function __construct(
        Brief $brief
    ) {
        $this->brief = $brief;
    }

    /**
     * @inheritDoc
     */
    public function briefedVersion(): Version
    {
        return $this->brief->version();
    }

    /**
     * @inheritDoc
     */
    public function briefedRevisions(): array
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
    public function reviseRequest(object $request): object
    {
        $revisions = array_filter($this->brief->revisions(), static function ($revision): bool {
            return $revision instanceof RequestRevision
                || $revision instanceof ResponseRevision;
        });

        $upToDateRequest = array_reduce(
            $revisions,
            function (object $request, object $revision): object {
                /** @var RequestRevision|ResponseRevision $revision */
                if (!$revision->isApplicable($request)) {
                    return $request;
                }

                $revisedRequest = $revision instanceof RequestRevision
                    ? $revision->applyToRequest($request)
                    : $request;

                // A `$revision` could be and instance of both `RequestRevision`
                // and `ResponseRevision` at the same time.
                if ($revision instanceof ResponseRevision) {
                    $this->notes[] = [
                        'revision' => $revision,
                        'request' => $revisedRequest,
                    ];
                }

                return $revisedRequest;
            },
            $request
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
            throw new RequestUnaware(
                'reviseRequest()',
                'reviseResponse()'
            );
        }

        return array_reduce(
            array_reverse($this->notes),
            static function (object $response, array $note): object {
                /** @var ResponseRevision $revision */
                $revision = $note['revision'];

                return $revision->applyToResponse($response, $note['request']);
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
