<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Double\Revision;

use DSLabs\Redaktor\Revision\MessageRevision;

final class DummyMessageRevision implements MessageRevision
{
    public function isApplicable(object $request): bool { }

    public function applyToRequest(object $request): object { }

    public function applyToResponse(object $response): object { }
}