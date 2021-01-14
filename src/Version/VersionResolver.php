<?php

namespace DSLabs\Redaktor\Version;

use DSLabs\Redaktor\Exception\InvalidArgumentException;

final class VersionResolver
{
    /**
     * @var Strategy[]
     */
    private $strategies;

    public function __construct(array $strategies)
    {
        self::ensureStrategyInstances($strategies);

        $this->strategies = $strategies;
    }

    /**
     * Resolve the target version for the given $request.
     *
     * @param object $request
     * @return Version
     */
    public function resolve(object $request): Version
    {
        foreach ($this->strategies as $strategy) {
            try {
                return $strategy->resolve($request);
            } catch (UnresolvedVersionException $exception) {
                // Ignore exception
            }
        }

        throw new UnresolvedVersionException($request);
    }

    private static function ensureStrategyInstances(array $strategies): void
    {
        foreach ($strategies as $strategy) {
            if (!$strategy instanceof Strategy) {
                throw new InvalidArgumentException([Strategy::class], $strategy);
            }
        }
    }
}
