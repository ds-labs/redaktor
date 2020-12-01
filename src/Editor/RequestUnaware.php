<?php

declare(strict_types=1);

namespace DSLabs\Redaktor\Editor;

final class RequestUnaware extends \RuntimeException
{
    public function __construct(
        string $requiredMethodCall,
        string $currentMethodCall
    ) {
        parent::__construct(
            "Unable to revise the response without having previously revised the request.
            `{$requiredMethodCall}` must be called before calling `{$currentMethodCall}`"
        );
    }
}
