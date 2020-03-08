<?php

declare(strict_types=1);

namespace DSLabs\Redaktor;

use Psr\Http\Message\RequestInterface;

final class Brief
{
    /**
     * @var RequestInterface
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
        RequestInterface $request,
        array $revisions
    ) {
        self::validateRevisions($revisions);

        $this->request = $request;
        $this->revisions = $revisions;
    }

    public function request(): RequestInterface
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
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s instance expected. Got: %s.',
                        Revision::class,
                        gettype($revision)
                    )
                );
            }
        }
    }
}