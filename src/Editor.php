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

    private $applicableRevisions = [];

    public function __construct(
        Brief $brief
    ) {
        $this->brief = $brief;
    }

    /**
     * Revise the Request given in the Brief
     */
    public function reviseRequest(): ServerRequestInterface
    {
        return array_reduce(
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
    }

    /**
     * Revise the given Response based on the applicable revisions for
     * the Request indicated in the Brief.
     */
    public function reviseResponse(ResponseInterface $response): ResponseInterface
    {
        // @todo: check if the applicable revisions are already known.
        $this->reviseRequest();

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
}
