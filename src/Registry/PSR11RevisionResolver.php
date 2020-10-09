<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Revision\Revision;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class PSR11RevisionResolver implements RevisionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve(RevisionDefinition $revisionDefinition): Revision
    {
        $revision = $revisionDefinition();

        if (is_string($revision)) {
            try {
                $revision = $this->container->get($revision);
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
                throw new UnableToResolveRevisionDefinition($revision);
            }
        }

        if ($revision instanceof Revision) {
            return $revision;
        }

        throw new UnableToResolveRevisionDefinition($revision);
    }
}