<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Version;

final class Version
{
    /**
     * @var self[]
     */
    private static $available = [];

    /**
     * @var string
     */
    private $version;

    public static function setList(array $versions): void
    {
        self::$available = [];

        foreach ($versions as $version) {
            self::$available[] = new self($version);
        }
    }

    public function __construct(string $version)
    {
        $this->version = $version;
    }

    public function isBefore(Version $version): bool
    {
        return self::distanceBetweenVersions($this, $version) > 0;
    }

    public function isSameOrBefore(Version $version): bool
    {
        return self::distanceBetweenVersions($this, $version) >= 0;
    }

    public function isSame(Version $version): bool
    {
        return (string)$this === (string)$version;
    }

    public function isAfter(Version $version): bool
    {
        return self::distanceBetweenVersions($this, $version) < 0;
    }

    public function isSameOrAfter(Version $version): bool
    {
        return self::distanceBetweenVersions($this, $version) <= 0;
    }

    public function __toString(): string
    {
        return $this->version;
    }

    private static function distanceBetweenVersions(Version $actual, Version $other): int
    {
        $indexOther = array_search($other, self::$available, false);
        $indexActual = array_search($actual, self::$available, false);

        return $indexOther - $indexActual;
    }
}
