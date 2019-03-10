<?php

namespace Exception;

use Redaktor\Revision;

final class MutationException extends \Exception
{
//    public function __construct($message)
//    {
//        parent::__construct($message);
//    }

    public static function inRevision(Revision $revision): self
    {
        $revisionClassName = get_class($revision);
        $message = "Revision [{$revisionClassName}] returned same received instance. Revision must be immutable.";
        return new self($message);
    }
}