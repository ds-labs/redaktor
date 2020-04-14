<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Revision;

interface Supersedes
{
    public function supersedes(MessageRevision $revision): bool;
}