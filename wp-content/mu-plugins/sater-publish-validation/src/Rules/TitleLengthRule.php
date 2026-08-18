<?php

declare(strict_types=1);

namespace Sater\PublishValidation\Rules;

use Sater\PublishValidation\RuleInterface;
use Sater\PublishValidation\Snapshot;
use Sater\PublishValidation\Violation;

final class TitleLengthRule implements RuleInterface
{
    public function __construct(
        private readonly int $minLength = 3,
        private readonly int $warnLength = 60
    ) {
    }

    public function check(Snapshot $snapshot): array
    {
        $title = $snapshot->normalizedTitle();
        $length = $title === '' ? 0 : mb_strlen($title);

        if ($length === 0) {
            $message = $snapshot->postType === 'mod-manualinput'
                ? __('Modulen måste ha en titel.', 'sater-publish-validation')
                : __('Sidan måste ha en titel.', 'sater-publish-validation');

            return [
                new Violation(
                    'title.empty',
                    $message
                ),
            ];
        }

        $violations = [];

        if ($length < $this->minLength) {
            $violations[] = new Violation(
                'title.too_short',
                sprintf(
                    /* translators: %d: minimum number of characters */
                    __('Titeln måste vara minst %d tecken.', 'sater-publish-validation'),
                    $this->minLength
                )
            );
        }

        if ($length > $this->warnLength) {
            $violations[] = new Violation(
                'title.too_long',
                sprintf(
                    /* translators: 1: actual length, 2: recommended maximum */
                    __('Titeln är %1$d tecken. Rekommenderat max är %2$d tecken (SEO).', 'sater-publish-validation'),
                    $length,
                    $this->warnLength
                ),
                'warning'
            );
        }

        return $violations;
    }
}
