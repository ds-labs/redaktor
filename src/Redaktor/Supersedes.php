<?php

declare(strict_types=1);

namespace Redaktor;

interface Supersedes
{
    public function supersedes(Revision $revision): bool;
}