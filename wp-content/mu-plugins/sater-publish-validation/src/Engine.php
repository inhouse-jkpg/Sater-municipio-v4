<?php

declare(strict_types=1);

namespace Sater\PublishValidation;

final class Engine
{
    /**
     * @param array<int, RuleInterface> $rules
     */
    public function __construct(private readonly array $rules)
    {
    }

    /**
     * @return array<int, Violation>
     */
    public function check(Snapshot $snapshot): array
    {
        $violations = [];

        foreach ($this->rules as $rule) {
            foreach ($rule->check($snapshot) as $violation) {
                $violations[] = $violation;
            }
        }

        return $violations;
    }
}
