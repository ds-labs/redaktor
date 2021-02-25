<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Version\Version;

/**
 * List of in-memory registered revisions.
 */
final class InMemoryRegistry implements Registry
{
    /**
     * @var array
     */
    private $versionsDefinition;

    /**
     * Receives a list of revisions indexed by its version. Expected format:
     * [
     *      '2020-02-23' => [
     *          ConvertActiveToActivatedAtRevision::class,
     *          ReplaceAgeByDateOfBirthRevision::class,
     *      ],
     *      '2020-03-13' => [
     *          RemoveResourceRevision::class,
     *      ]
     * ]
     */
    public function __construct(
        array $versionsDefinition = []
    ) {
        $this->versionsDefinition = self::instantiateRevisionDefinitions($versionsDefinition);

        Version::setList(
            array_keys($this->versionsDefinition)
        );
    }

    /**
     * @inheritDoc
     */
    public function index(): array
    {
        return array_map(
            static function (string $version) {
                return new Version($version);
            },
            array_keys($this->versionsDefinition)
        );
    }

    /**
     * Retrieves a collection of all registered revision definitions.
     *
     * @return RevisionDefinition[]
     */
    public function retrieveAll(): array
    {
        return self::flatten($this->versionsDefinition);
    }

    /**
     * Retrieves a collection of the revision definitions since the given version.
     *
     * @return RevisionDefinition[]
     */
    public function retrieveSince(Version $version): array
    {
        if (!$this->versionsDefinition) {
            return [];
        }

        $index = array_search((string)$version, array_keys($this->versionsDefinition), true);
        if ($index === false) {
            return [];
        }

        $applicableVersionsDefinition = array_slice(
            $this->versionsDefinition,
            $index
        );

        return self::flatten($applicableVersionsDefinition);
    }

    private static function instantiateRevisionDefinitions(array $versionsDefinition): array
    {
        $values = array_map(
            static function (array $versionDefinition, string $version): array {
                if (!$versionDefinition) {
                    throw InvalidVersionDefinitionException::empty($version);
                }

                return array_map(
                    static function ($revisionDefinition): RevisionDefinition {
                        return new RevisionDefinition($revisionDefinition);
                    },
                    $versionDefinition
                );
            },
            $versionsDefinition,
            $keys = array_keys($versionsDefinition)
        );

        return array_combine(
            $keys,
            $values
        );
    }

    /**
     * @param array[] $versionsDefinition
     *
     * @return RevisionDefinition[]
     */
    private static function flatten(array $versionsDefinition): array
    {
        return array_reduce($versionsDefinition, 'array_merge', []);
    }
}
