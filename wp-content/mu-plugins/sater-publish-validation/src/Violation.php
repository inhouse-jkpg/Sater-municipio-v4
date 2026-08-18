<?php

declare(strict_types=1);

namespace Sater\PublishValidation;

final class Violation
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly string $severity = 'error'
    ) {
    }

    /**
     * @return array{code: string, message: string, severity: string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'severity' => $this->severity === 'warning' ? 'warning' : 'error',
        ];
    }
}
