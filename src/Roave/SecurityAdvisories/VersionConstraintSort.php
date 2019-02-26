<?php

declare(strict_types=1);

namespace Roave\SecurityAdvisories;

final class VersionConstraintSort
{
    public function __invoke(VersionConstraint $a, VersionConstraint $b) : int
    {
        $versionA = $a->getLowerBound() ?? $a->getUpperBound();
        $versionB = $b->getLowerBound() ?? $b->getUpperBound();

        if (isset($versionA, $versionB)) {
            return $versionA->isGreaterOrEqualThan($versionB) ? 1 : -1;
        }

        return 0;
    }
}
