<?php

namespace DSLabs\Redaktor\Editor;

use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;

interface EditorInterface
{
    /**
     * Retrieves the version that was briefed on.
     */
    public function briefedVersion(): Version;

    /**
     * Retrieves the list of revisions that was briefed on.
     *
     * @return Revision[]
     */
    public function briefedRevisions(): array;
}
