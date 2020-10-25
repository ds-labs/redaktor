<?php

namespace spec\DSLabs\Redaktor\Version;

use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VersionSpec extends ObjectBehavior
{
    function it_casts_to_string()
    {
        // Arrange
        $this->beConstructedWith('foo');

        // Act
        $this->__toString()
            // Assert
            ->shouldBe('foo');
    }
}
