<?php

declare(strict_types=1);

namespace Sater\PublishValidation\Rules;

use Sater\PublishValidation\ManualInputOutline;
use Sater\PublishValidation\RuleInterface;
use Sater\PublishValidation\Snapshot;
use Sater\PublishValidation\Violation;

final class VagueLinkTextRule implements RuleInterface
{
    /**
     * @var array<int, string>
     */
    private const PHRASES = [
        'klicka här',
        'här',
        'läs mer',
        'mer info',
        'länk',
    ];

    public function check(Snapshot $snapshot): array
    {
        $acf = isset($_POST['acf']) && is_array($_POST['acf']) ? $_POST['acf'] : null;
        $texts = array_merge(
            $this->textsFromHtml($snapshot->content . "\n" . $snapshot->moduleOutlineHtml),
            ManualInputOutline::linkLabelsForPost($snapshot->postId, $snapshot->postType, $acf)
        );

        $found = [];
        foreach ($texts as $text) {
            $normalized = $this->normalize($text);
            if ($normalized !== '' && isset($this->phraseMap()[$normalized])) {
                $found[$normalized] = true;
            }
        }

        if ($found === []) {
            return [];
        }

        $quoted = array_map(
            static fn (string $phrase): string => '"' . $phrase . '"',
            array_keys($found)
        );

        $list = implode(', ', $quoted);
        $message = count($found) === 1
            ? sprintf(
                /* translators: %s: quoted link text */
                __('Otydlig länktext: %s. Beskriv vart länken leder.', 'sater-publish-validation'),
                $list
            )
            : sprintf(
                /* translators: %s: quoted link texts */
                __('Otydliga länktexter: %s. Beskriv vart länken leder.', 'sater-publish-validation'),
                $list
            );

        return [
            new Violation('link.vague_text', $message, 'warning'),
        ];
    }

    /**
     * @return array<string, true>
     */
    private function phraseMap(): array
    {
        $phrases = apply_filters('sater_publish_validation_vague_link_phrases', self::PHRASES);
        $map = [];

        if (!is_array($phrases)) {
            return $map;
        }

        foreach ($phrases as $phrase) {
            if (!is_string($phrase)) {
                continue;
            }

            $normalized = $this->normalize($phrase);
            if ($normalized !== '') {
                $map[$normalized] = true;
            }
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    private function textsFromHtml(string $html): array
    {
        if (trim($html) === '' || !preg_match_all('/<a\b([^>]*)>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $texts = [];
        foreach ($matches as $match) {
            $attributes = $match[1] ?? '';
            $accessible = $this->attributeValue($attributes, 'aria-label');
            if ($accessible !== '') {
                $texts[] = $accessible;
                continue;
            }

            $texts[] = $match[2] ?? '';
        }

        return $texts;
    }

    private function attributeValue(string $attributes, string $name): string
    {
        $pattern = '/\b' . preg_quote($name, '/') . '\s*=\s*(["\'])(.*?)\1/i';
        if (!preg_match($pattern, $attributes, $match)) {
            return '';
        }

        return html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function normalize(string $text): string
    {
        $text = html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? $text));
        $text = preg_replace('/[\s\.\!\?\:\;…]+$/u', '', $text) ?? $text;

        return $text;
    }
}
