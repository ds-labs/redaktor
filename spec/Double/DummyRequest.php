<?php

declare(strict_types=1);

namespace spec\DSLabs\Redaktor\Double;

/**
 * Http Request representation.
 */
final class DummyRequest
{
    private $id;

    public function __construct()
    {
        // Generate a random identifier so that two instances can be
        // identified as different if performing a non-strict comparison,
        // i.e. using a double equal sign (==) or `shouldBeLike` method.
        $this->id = mt_rand();
    }
}
