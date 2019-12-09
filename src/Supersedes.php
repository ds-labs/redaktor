<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

interface Supersedes
{
    public function supersedes(Revision $revision): bool;
}