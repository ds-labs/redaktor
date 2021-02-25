<?php

namespace spec\DSLabs\Redaktor\Version;

use DSLabs\Redaktor\Exception\InvalidArgumentException;
use DSLabs\Redaktor\Version\Version;
use PhpSpec\ObjectBehavior;

/**
 * @see Version
 */
class VersionSpec extends ObjectBehavior
{
    function let()
    {
        // Reset Version static properties, so they don't interfere between scenarios.
        self::resetInstance();
    }

    function it_throws_an_exception_when_initialised_with_a_non_string()
    {
        // Arrange
        $this->beConstructedWith('');

        // Assert
        $this->shouldThrow(InvalidArgumentException::class)
            // Act
            ->during('setList', [[123]]);
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

    function it_throws_an_error_when_calling_isBefore_before_initialising_the_version_list()
    {
        // Arrange
        $this->beConstructedWith('foo');

        // Assert
        $this->shouldThrow(\RuntimeException::class)
            // Act
            ->during('isBefore', [new Version('foo')]);
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


    function it_throws_an_error_when_calling_isSameOrBefore_before_initialising_the_version_list()
    {
        // Arrange
        $this->beConstructedWith('foo');

        // Assert
        $this->shouldThrow(\RuntimeException::class)
            // Act
            ->during('isSameOrBefore', [new Version('foo')]);
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

    function it_throws_an_error_when_calling_isAfter_before_initialising_the_version_list()
    {
        // Arrange
        $this->beConstructedWith('foo');

        // Assert
        $this->shouldThrow(\RuntimeException::class)
            // Act
            ->during('isAfter', [new Version('foo')]);
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

    function it_throws_an_error_when_calling_isSameOrAfter_before_initialising_the_version_list()
    {
        // Arrange
        $this->beConstructedWith('foo');

        // Assert
        $this->shouldThrow(\RuntimeException::class)
            // Act
            ->during('isSameOrAfter', [new Version('foo')]);
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

    /*
     * Workaround to reset the static properties.
     */
    private static function resetInstance(): void
    {
        self::resetStaticProperty('list', []);
        self::resetStaticProperty('initialised', false);
    }

    private static function resetStaticProperty(string $name, $value): void
    {
        $listProperty = (new \ReflectionClass(Version::class))->getProperty($name);
        $listProperty->setAccessible(true);
        $listProperty->setValue($value);
        $listProperty->setAccessible(false);
    }
}
