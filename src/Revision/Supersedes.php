<?php

namespace DSLabs\Redaktor\Revision;

interface Supersedes
{
    public function supersedes(Revision $revision): bool;
}