<?php

namespace DSLabs\Redaktor\Registry;

use DSLabs\Redaktor\Version\Version;

interface Registry
{
    /**
     * Retrieves the list of available versions.
     *
     * @return Version[]
     */
    public function index(): array;

    /**
     * Retrieves a collection of all registered revision definitions.
     *
     * @return RevisionDefinition[]
     */
    public function retrieveAll(): array;

    /**
     * Retrieves a collection of the revision definitions since the given version.
     *
     * @return RevisionDefinition[]
     */
    public function retrieveSince(Version $version): array;
}
