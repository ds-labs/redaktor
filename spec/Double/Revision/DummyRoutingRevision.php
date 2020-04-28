<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Double\Revision;

use DSLabs\Redaktor\Revision\RoutingRevision;

final class DummyRoutingRevision implements RoutingRevision
{
    public function __invoke(iterable $routes): iterable { }
}