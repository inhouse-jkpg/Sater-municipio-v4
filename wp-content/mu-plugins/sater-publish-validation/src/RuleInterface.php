<?php

declare(strict_types=1);

namespace Sater\PublishValidation;

interface RuleInterface
{
    /**
     * @return array<int, Violation>
     */
    public function check(Snapshot $snapshot): array;
}
