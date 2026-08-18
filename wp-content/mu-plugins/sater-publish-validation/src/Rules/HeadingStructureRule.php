<?php

declare(strict_types=1);

namespace Sater\PublishValidation\Rules;

use Sater\PublishValidation\RuleInterface;
use Sater\PublishValidation\Snapshot;
use Sater\PublishValidation\Violation;

final class HeadingStructureRule implements RuleInterface
{
    public function check(Snapshot $snapshot): array
    {
        $levels = $this->headingLevels($snapshot->headingHtml());
        if ($levels === []) {
            return [];
        }

        $violations = [];
        $contentH1Count = 0;

        foreach ($levels as $level) {
            if ($level === 1) {
                $contentH1Count++;
            }
        }

        if ($contentH1Count > 0) {
            $violations[] = new Violation(
                'heading.content_h1',
                __('Innehållet innehåller en H1-rubrik. Sidtiteln blir redan H1, så använd H2 och nedåt (även i moduler).', 'sater-publish-validation')
            );
        }

        $previous = $snapshot->normalizedTitle() !== '' || $snapshot->postType === 'mod-manualinput' ? 1 : 0;
        $seenSkips = [];

        foreach ($levels as $level) {
            if ($previous > 0 && $level > $previous + 1) {
                $skipKey = $previous . '-' . $level;
                if (!isset($seenSkips[$skipKey])) {
                    $seenSkips[$skipKey] = true;
                    $violations[] = new Violation(
                        'heading.skipped_level',
                        sprintf(
                            /* translators: 1: previous heading level, 2: found heading level */
                            __('Rubriknivå hoppas över (H%1$d till H%2$d).', 'sater-publish-validation'),
                            $previous,
                            $level
                        )
                    );
                }
            }

            $previous = $level;
        }

        return $violations;
    }

    /**
     * @return array<int, int>
     */
    private function headingLevels(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }

        if (!preg_match_all('/<h([1-6])\b[^>]*>/i', $content, $matches)) {
            return [];
        }

        return array_map('intval', $matches[1]);
    }
}
