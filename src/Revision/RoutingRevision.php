<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Revision;

interface RoutingRevision extends Revision
{
    public function __invoke(iterable $routes): iterable;
}
