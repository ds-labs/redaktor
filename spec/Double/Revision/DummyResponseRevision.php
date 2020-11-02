<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Double\Revision;

use DSLabs\Redaktor\Revision\ResponseRevision;

final class DummyResponseRevision implements ResponseRevision
{
    public function isApplicable(object $request): bool { }

    public function applyToResponse(object $request, object $response): object { }
}
