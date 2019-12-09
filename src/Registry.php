<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

interface Registry
{
    public function retrieveAll(): array;

    public function retrieveSince(string $version): array;
}