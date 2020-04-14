<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Revision;

interface RoutingRevision
{
    public function __invoke(iterable $routes): iterable;
}
