<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use DSLabs\Redaktor\Exception\MutationException;
use DSLabs\Redaktor\Registry\MessageRevision;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Given a Brief is able to revise a Request and/or a Response.
 */
final class Editor
{
    /**
     * @var Brief
     */
    private $brief;

    /**
     * @var bool
     */
    protected $requestIsRevised = false;

    private $applicableRevisions = [];

    public function __construct(
        Brief $brief
    ) {
        $this->brief = $brief;
    }

    /**
     * Revise the Request given in the Brief.
     */
    public function reviseRequest(): ServerRequestInterface
    {
        $upToDateRequest = array_reduce(
            $this->brief->revisions(),
            function(
                RequestInterface $request,
                MessageRevision $revision
            ) {
                if (!$revision->isApplicable($request)) {
                    return $request;
                }

                $this->applicableRevisions[] = $revision;
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
     * the Request indicated in the Brief.
     */
    public function reviseResponse(ResponseInterface $response): ResponseInterface
    {
        if (!$this->requestIsRevised) {
            $this->reviseRequest();
        }

        return array_reduce(
            array_reverse($this->applicableRevisions),
            static function(ResponseInterface $response, MessageRevision $revision) {
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
