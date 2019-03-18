<?php

declare(strict_types=1);

namespace Redaktor;

use function count;
use Redaktor\Exception\InvalidVersionDefinitionException;

final class InMemoryRegistry implements Registry
{
    /**
     * @var array
     */
    private $indexedRevisions;

    public function __construct($indexedRevisions = [])
    {
        foreach ($indexedRevisions as $version => $revisions) {
            if (!$revisions) {
                throw new InvalidVersionDefinitionException(
                    'Empty version definition.' // @todo - Improve message
                );
            }

            foreach ($revisions as $revision) {
                if (!$revision instanceof \Closure) {
                    throw new InvalidVersionDefinitionException(
                        'Revision must be closure.'
                    );
                }
            }
        }

        $this->indexedRevisions = $indexedRevisions;
    }

    public function retrieveAll(): array
    {
        return self::reduce($this->indexedRevisions);
    }

    public function retrieveSince(string $version): array
    {
        $index = array_search($version, array_keys($this->indexedRevisions), true);
        $slice = array_slice($this->indexedRevisions, $index, count($this->indexedRevisions) - $index);

        return self::reduce($slice);
    }

    private static function reduce($indexedRevisions): array
    {
        return array_reduce($indexedRevisions, 'array_merge', []);
    }
}
