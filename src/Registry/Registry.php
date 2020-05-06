<?php

namespace DSLabs\Redaktor\Registry;

interface Registry
{
    public function retrieveAll(): array;

    public function retrieveSince(string $version): array;
}