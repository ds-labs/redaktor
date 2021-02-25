<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Version;

use DSLabs\Redaktor\Exception\InvalidArgumentException;

final class Version
{
    /**
     * @var self[]
     */
    private static $list = [];

    /**
     * @var bool
     */
    private static $initialised = false;

    /**
     * @var string
     */
    private $version;

    /**
     * @internal
     *
     * @version string[]
     */
    public static function setList(array $versions): void
    {
        self::guardAgainstInvalidVersionNames($versions);

        self::$list = $versions;
        self::$initialised = true;
    }

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    public function isBefore(Version $version): bool
    {
        self::guardAgainstUninitialised();

        return self::distanceBetweenVersions($this, $version) > 0;
    }

    public function isSameOrBefore(Version $version): bool
    {
        self::guardAgainstUninitialised();

        return self::distanceBetweenVersions($this, $version) >= 0;
    }

    public function isSame(Version $version): bool
    {
        return (string)$this === (string)$version;
    }

    public function isAfter(Version $version): bool
    {
        self::guardAgainstUninitialised();

        return self::distanceBetweenVersions($this, $version) < 0;
    }

    public function isSameOrAfter(Version $version): bool
    {
        self::guardAgainstUninitialised();

        return self::distanceBetweenVersions($this, $version) <= 0;
    }

    public function __toString(): string
    {
        return $this->version;
    }

    public static function guardAgainstInvalidVersionNames(array $versions): void
    {
        foreach ($versions as $version) {
            if (!is_string($version)) {
                throw new InvalidArgumentException(['string'], $version);
            }
        }
    }

    public static function guardAgainstUninitialised(): void
    {
        if (!self::$initialised) {
            throw new \RuntimeException();
        }
    }

    private static function distanceBetweenVersions(Version $actual, Version $other): int
    {
        $indexOther = array_search((string)$other, self::$list, true);
        $indexActual = array_search((string)$actual, self::$list, true);

        return $indexOther - $indexActual;
    }
}
