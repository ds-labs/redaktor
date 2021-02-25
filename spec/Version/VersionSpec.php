<?php

namespace spec\DSLabs\Redaktor\Version;

use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;

class VersionSpec extends ObjectBehavior
{
    public function let()
    {
        // Arrange
        $this->beConstructedWith('');
    }

    function it_casts_to_string()
    {
        // Arrange
        $this->beConstructedWith('foo');

        // Act
        $this->__toString()
            // Assert
            ->shouldBe('foo');
    }

    function it_is_before()
    {
        // Arrange
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isBefore(new Version('bar'))
            // Assert
            ->shouldBe(true);
    }

    function it_is_not_before()
    {
        // Arrange
        $this->beConstructedWith('bar');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isBefore(new Version('foo'))
            // Assert
            ->shouldBe(false);
    }

    function it_is_same_when_calling_same_or_before()
    {
        // Arrange
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
        ]);

        // Act
        $this->isSameOrBefore(new Version('foo'))
            // Assert
            ->shouldBe(true);
    }

    function it_is_not_same_when_calling_same_or_before()
    {
        // Arrange
        $this->beConstructedWith('bar');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isSameOrBefore(new Version('foo'))
            // Assert
            ->shouldBe(false);
    }

    function it_is_before_when_calling_same_or_before()
    {
        // Arrange
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isSameOrBefore(new Version('bar'))
            // Assert
            ->shouldBe(true);
    }

    function it_is_same()
    {
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
        ]);

        // Act
        $this->isSame(
            new Version('foo')
        )
            // Assert
            ->shouldBe(true);
    }

    function it_is_not_same()
    {
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isSame(
            new Version('bar')
        )
            // Assert
            ->shouldBe(false);
    }

    function it_is_after()
    {
        // Arrange
        $this->beConstructedWith('bar');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isAfter(new Version('foo'))
            // Assert
            ->shouldBe(true);
    }

    function it_is_not_after()
    {
        // Arrange
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isAfter(new Version('bar'))
            // Assert
            ->shouldBe(false);
    }

    function it_is_same_when_calling_same_or_after()
    {
        // Arrange
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
        ]);

        // Act
        $this->isSameOrAfter(new Version('foo'))
            // Assert
            ->shouldBe(true);
    }

    function it_is_not_same_when_calling_same_or_after()
    {
        // Arrange
        $this->beConstructedWith('foo');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isSameOrAfter(new Version('bar'))
            // Assert
            ->shouldBe(false);
    }

    function it_is_after_when_calling_same_or_after()
    {
        // Arrange
        $this->beConstructedWith('bar');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this->isSameOrAfter(new Version('foo'))
            // Assert
            ->shouldBe(true);
    }

    function it_resets_the_list()
    {
        // Arrange
        $this->beConstructedWith('baz');
        $this::setList([
            'foo',
            'bar',
        ]);

        // Act
        $this::setList([
            'baz',
            'quz',
        ]);

        // Assert
        $this->isSame(new Version('baz'))
            ->shouldReturn(true);
        $this->isBefore(new Version('quz'))
            ->shouldReturn(true);
    }
}
