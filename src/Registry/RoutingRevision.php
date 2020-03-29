<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

interface RoutingRevision
{
    public function __invoke($routes);
}
