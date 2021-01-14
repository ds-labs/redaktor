<?php

namespace DSLabs\Redaktor\Version;

interface Strategy
{
    public function resolve(object $request): Version;
}
