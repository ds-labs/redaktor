<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\RoutingRevision;
use DSLabs\Redaktor\Version\Version;

/**
 * Given a Brief is able to revise the application routes from/to the briefed version.
 */
final class RoutingEditor implements RoutingEditorInterface
{
    /**
     * @var Brief
     */
    private $brief;

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
        foreach ($this->briefedRevisions() as $revision) {
            if ($revision instanceof RoutingRevision) {
                $routes = $revision($routes);
            }
        }

        return $routes;
    }
}