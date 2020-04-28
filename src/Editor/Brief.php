<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\Revision;

final class Brief
{
    /**
     * @var object
     */
    private $request;

    /**
     * @var Revision[]
     */
    private $revisions;

    /**
     * @param Revision[] $revisions
     */
    public function __construct(
        object $request,
        array $revisions
    ) {
        self::validateRevisions($revisions);

        $this->request = $request;
        $this->revisions = $revisions;
    }

    public function request(): object
    {
        return $this->request;
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