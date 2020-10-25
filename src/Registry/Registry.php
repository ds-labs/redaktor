<?php

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Version\Version;

interface Registry
{
    public function retrieveAll(): array;

    public function retrieveSince(Version $version): array;
}