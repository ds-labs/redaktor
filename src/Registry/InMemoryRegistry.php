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
        foreach ($indexedRevisionFactories as $version => $revisionDefinitions) {
            if (!$revisionDefinitions) {
                throw new InvalidVersionDefinitionException(
                    'Empty version definition.' // @todo - Improve message
                );
            }

            foreach ($revisionDefinitions as $revisionDefinition) {
                if (!self::isRevisionDefinition($revisionDefinition)) {
                    throw new InvalidVersionDefinitionException(
                        sprintf(
                            'Expected %s, or instance of %s or %s. Got: %s',
                            Closure::class,
                            RoutingRevision::class,
                            MessageRevision::class,
                            is_object($revisionDefinition)
                                ? get_class($revisionDefinition)
                                : $revisionDefinition
                        )
                    );
                }
            }
        }
    }

    private static function isRevisionDefinition($revision): bool
    {
        if ($revision instanceof Closure) {
            return true;
        }

        if (!class_exists($revision)) {
            return false;
        }

        $interfaces = class_implements($revision);

        return in_array(MessageRevision::class, $interfaces, true)
            || in_array(RoutingRevision::class, $interfaces, true);
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
