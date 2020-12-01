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

    /**
     * @var Revision|string
     */
    private $resolved;

    /**
     * @param $definition Closure|Revision|string
     */
    public function __construct($definition)
    {
        $this->factory = self::createFactory($definition);
    }

    /**
     * @return string|Revision
     */
    public function __invoke()
    {
        if ($this->resolved) {
            return $this->resolved;
        }

        return $this->resolved = call_user_func($this->factory);
    }

    private static function createFactory($definition): Closure
    {
        if ($definition instanceof Closure) {
            return $definition;
        }

        if ($definition instanceof Revision) {
            return self::wrapInClosure($definition);
        }

        if (
            is_string($definition)
        ) {
            return self::wrapInClosure($definition);
        }

        throw InvalidRevisionDefinition::invalidDefinition($definition);
    }

    /**
     * @param Revision|string $revision
     * @return Closure
     */
    private static function wrapInClosure($revision): Closure
    {
        return static function () use ($revision) {
            return $revision;
        };
    }
}
