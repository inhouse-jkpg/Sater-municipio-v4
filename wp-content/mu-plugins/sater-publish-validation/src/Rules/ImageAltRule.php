<?php

declare(strict_types=1);

namespace Sater\PublishValidation\Rules;

use Sater\PublishValidation\RuleInterface;
use Sater\PublishValidation\Snapshot;
use Sater\PublishValidation\Violation;

final class ImageAltRule implements RuleInterface
{
    public function check(Snapshot $snapshot): array
    {
        $violations = [];

        if ($snapshot->featuredImageId > 0 && !$this->attachmentHasAlt($snapshot->featuredImageId)) {
            $violations[] = new Violation(
                'image.featured_alt',
                __('Utvald bild saknar alt-text.', 'sater-publish-validation')
            );
        }

        $missingInContent = $this->countContentImagesMissingAlt($snapshot->content);
        if ($missingInContent === 1) {
            $violations[] = new Violation(
                'image.content_alt',
                __('En bild i brödtexten saknar alt-text. Dekorativa bilder ska ha tom alt-text (alt="").', 'sater-publish-validation')
            );
        } elseif ($missingInContent > 1) {
            $violations[] = new Violation(
                'image.content_alt',
                sprintf(
                    /* translators: %d: number of images */
                    __('%d bilder i brödtexten saknar alt-text. Dekorativa bilder ska ha tom alt-text (alt="").', 'sater-publish-validation'),
                    $missingInContent
                )
            );
        }

        $missingAttachments = 0;
        foreach ($snapshot->imageIds as $imageId) {
            if ($imageId === $snapshot->featuredImageId) {
                continue;
            }

            if ($imageId > 0 && wp_attachment_is_image($imageId) && !$this->attachmentHasAlt($imageId)) {
                $missingAttachments++;
            }
        }

        if ($missingAttachments === 1) {
            $violations[] = new Violation(
                'image.attachment_alt',
                __('En bild i sidans fält saknar alt-text i mediabiblioteket.', 'sater-publish-validation')
            );
        } elseif ($missingAttachments > 1) {
            $violations[] = new Violation(
                'image.attachment_alt',
                sprintf(
                    /* translators: %d: number of images */
                    __('%d bilder i sidans fält saknar alt-text i mediabiblioteket.', 'sater-publish-validation'),
                    $missingAttachments
                )
            );
        }

        return $violations;
    }

    private function attachmentHasAlt(int $attachmentId): bool
    {
        $alt = get_post_meta($attachmentId, '_wp_attachment_image_alt', true);

        return is_string($alt) && trim($alt) !== '';
    }

    private function countContentImagesMissingAlt(string $content): int
    {
        if (trim($content) === '' || !preg_match_all('/<img\b[^>]*>/i', $content, $matches)) {
            return 0;
        }

        $missing = 0;
        foreach ($matches[0] as $tag) {
            if (!preg_match('/\balt\s*=/i', $tag)) {
                $missing++;
            }
        }

        return $missing;
    }
}
