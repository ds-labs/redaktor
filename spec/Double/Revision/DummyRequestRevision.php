<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Double\Revision;

use DSLabs\Redaktor\Revision\RequestRevision;

final class DummyRequestRevision implements RequestRevision
{
    public function isApplicable(object $request): bool
    {
    }

    public function applyToRequest(object $request): object
    {
    }
}
