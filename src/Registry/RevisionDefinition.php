<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use Closure;
use DSLabs\Redaktor\Revision\Revision;

final class RevisionDefinition
{
    /**
     * @var Closure
     */
    private $factory;

    public function __construct($definition)
    {
        $this->factory = self::createFactory($definition);
    }

    public function getFactory(): Closure
    {
        return $this->factory;
    }

    private static function createFactory($definition): Closure
    {
        if ($definition instanceof Closure) {
            return $definition;
        }

        if (
            is_string($definition)
            && class_exists($definition)
            && in_array(Revision::class, class_implements($definition), true)
        ) {
            return self::wrapInClosure($definition);
        }

        if ($definition instanceof Revision) {
            return self::wrapInClosure($definition);
        }

        throw InvalidRevisionDefinition::invalidDefinition($definition);
    }

    /**
     * @param Revision|string $definition
     * @return Closure
     */
    private static function wrapInClosure($definition): Closure
    {
        return static function () use ($definition) {
            return $definition;
        };
    }
}