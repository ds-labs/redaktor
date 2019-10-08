<?php

namespace Redaktor;

interface Supersedes
{
    public function supersedes(Revision $revision): bool;
}