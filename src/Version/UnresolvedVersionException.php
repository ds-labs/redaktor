<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Version;

final class UnresolvedVersionException extends \RuntimeException
{
    /**
     * @var object
     */
    private $request;

    public function __construct(object $request)
    {
        parent::__construct(
            'Unable to resolve a version for the given request.'
        );

        $this->request = $request;
    }

    public function request(): object
    {
        return $this->request;
    }
}
