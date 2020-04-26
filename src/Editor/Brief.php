<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\MessageRevision;
use DSLabs\Redaktor\Revision\RequestRevision;
use DSLabs\Redaktor\Revision\ResponseRevision;
use DSLabs\Redaktor\Revision\RoutingRevision;

final class Brief
{
    /**
     * @var object
     */
    private $request;

    /**
     * @var MessageRevision[]|RoutingRevision[]
     */
    private $revisions;

    /**
     * @param RequestRevision[]|ResponseRevision[]|RoutingRevision[] $revisions
     */
    public function __construct(
        object $request,
        array $revisions
    ) {
        self::validateRevisions($revisions);

        $this->request = $request;
        $this->revisions = $revisions;
    }

    public function request(): object
    {
        return $this->request;
    }

    /**
     * @return MessageRevision[]
     */
    public function revisions(): array
    {
        return $this->revisions;
    }

    private static function validateRevisions(array $revisions): void
    {
        foreach ($revisions as $revision) {
            if (
                !($revision instanceof RequestRevision
                || $revision instanceof ResponseRevision
                || $revision instanceof RoutingRevision)
            ) {
                $type = is_object($revision)
                    ? get_class($revision)
                    : gettype($revision);

                throw new \InvalidArgumentException(
                    sprintf(
                        '%s instance expected. Got: %s.',
                        MessageRevision::class,
                        $type
                    )
                );
            }
        }
    }
}