<?php

declare(strict_types=1);

namespace Redaktor;

use Closure;
use function count;
use Redaktor\Exception\InvalidVersionDefinitionException;

final class InMemoryRegistry implements Registry
{
    /**
     * @var array
     */
    private $indexedRevisionFactories;

    public function __construct(array $indexedRevisionFactories = [])
    {
        foreach ($indexedRevisionFactories as $version => $revisionFactories) {
            if (!$revisionFactories) {
                throw new InvalidVersionDefinitionException(
                    'Empty version definition.' // @todo - Improve message
                );
            }

            foreach ($revisionFactories as $revisionFactory) {
                if (!$revisionFactory instanceof Closure) {
                    throw new InvalidVersionDefinitionException(
                        'Revision must be defined as a closure.'
                    );
                }
            }
        }

        $this->indexedRevisionFactories = $indexedRevisionFactories;
    }

    public function retrieveAll(): array
    {
        return self::instantiate(
            self::flatten($this->indexedRevisionFactories)
        );
    }

    public function retrieveSince(string $version): array
    {
        $index = array_search($version, array_keys($this->indexedRevisionFactories), true);
        $applicableRevisionsFactories = array_slice(
            $this->indexedRevisionFactories,
            $index,
            count($this->indexedRevisionFactories) - $index
        );

        return self::instantiate(
            self::flatten($applicableRevisionsFactories)
        );
    }

    /**
     * @param array $revisionFactories
     *
     * @return Revision[]
     */
    private static function instantiate(array $revisionFactories): array
    {
        return array_map(static function (Closure $revisionFactory) {
            return $revisionFactory();
        }, $revisionFactories);
    }

    /**
     * @param array $indexedRevisionFactories
     *
     * @return Closure[]
     */
    private static function flatten(array $indexedRevisionFactories): array
    {
        return array_reduce($indexedRevisionFactories, 'array_merge', []);
    }
}
