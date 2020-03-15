<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use Closure;
use DSLabs\Redaktor\Exception\InvalidVersionDefinitionException;

/**
 * List of in-memory registered revisions.
 */
final class InMemoryRegistry implements Registry
{
    /**
     * @var array
     */
    private $indexedRevisionFactories;

    /**
     * Receives a list of revisions indexed by its version. Expected format:
     * [
     *      '2020-02-23' => [
     *          static function () {
     *              return new RenameResourceRevision();
     *          },
     *          static function () {
     *              return new GzipCompressionRevision();
     *          },
     *      ],
     *      '2020-03-13' => [
     *          static function () {
     *              return new RemoveResourceRevision();
     *          },
     *      ]
     * ]
     */
    public function __construct(
        array $indexedRevisionFactories = []
    ) {
        self::validate($indexedRevisionFactories);

        $this->indexedRevisionFactories = $indexedRevisionFactories;
    }

    /**
     * Retrieves a collection of all registered revisions.
     *
     * @return array|Closure[]
     */
    public function retrieveAll(): array
    {
        return self::flatten($this->indexedRevisionFactories);
    }

    /**
     * Retrieves a collection of the revisions since the given $version.
     *
     * @return array|Closure[]
     */
    public function retrieveSince(string $version): array
    {
        $index = array_search($version, array_keys($this->indexedRevisionFactories), true);
        $applicableRevisionsFactories = array_slice(
            $this->indexedRevisionFactories,
            $index
        );

        return self::flatten($applicableRevisionsFactories);
    }

    private static function validate(array $indexedRevisionFactories): void
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
                        'Revision Factory must be defined as a Closure. Got: ' . gettype($revisionFactory) . '.'
                    );
                }
            }
        }
    }

    /**
     * @param array[] $indexedRevisionFactories
     *
     * @return Closure[]
     */
    private static function flatten(array $indexedRevisionFactories): array
    {
        return array_reduce($indexedRevisionFactories, 'array_merge', []);
    }
}
