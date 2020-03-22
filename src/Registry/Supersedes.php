<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

interface Supersedes
{
    public function supersedes(MessageRevision $revision): bool;
}