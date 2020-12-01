<?php

namespace DSLabs\Redaktor\Version;

interface VersionResolver
{
    public function resolve(object $request): Version;
}
