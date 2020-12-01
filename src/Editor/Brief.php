<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;

final class Brief
{
    /**
     * @var Version
     */
    private $version;

    /**
     * @var Revision[]
     */
    private $revisions;

    /**
     * @param Revision[] $revisions
     */
    public function __construct(
        Version $version,
        array $revisions
    ) {
        self::validateRevisions($revisions);

        $this->version = $version;
        $this->revisions = $revisions;
    }

    public function version(): Version
    {
        return $this->version;
    }

    /**
     * @return Revision[]
     */
    public function revisions(): array
    {
        return $this->revisions;
    }

    private static function validateRevisions(array $revisions): void
    {
        foreach ($revisions as $revision) {
            if (!$revision instanceof Revision) {
                $type = is_object($revision)
                    ? get_class($revision)
                    : gettype($revision);

                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected instance of %s. Got: %s.',
                        Revision::class,
                        $type
                    )
                );
            }
        }
    }
}
