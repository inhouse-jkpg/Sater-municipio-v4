<?php

declare(strict_types=1);

namespace Sater\PublishValidation;

final class Snapshot
{
    /**
     * @param array<int, int> $imageIds
     */
    public function __construct(
        public readonly int $postId,
        public readonly string $postType,
        public readonly string $title,
        public readonly string $content,
        public readonly int $featuredImageId,
        public readonly array $imageIds,
        public readonly string $moduleOutlineHtml = ''
    ) {
    }

    public function headingHtml(): string
    {
        return $this->content . "\n" . $this->moduleOutlineHtml;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $postarr
     */
    public static function fromInsertData(array $data, array $postarr): self
    {
        $postId = isset($postarr['ID']) ? (int) $postarr['ID'] : 0;
        $postType = isset($data['post_type']) ? (string) $data['post_type'] : '';
        $title = isset($data['post_title']) ? (string) $data['post_title'] : '';
        $content = isset($data['post_content']) ? (string) $data['post_content'] : '';
        $acf = isset($_POST['acf']) && is_array($_POST['acf']) ? $_POST['acf'] : null;

        return new self(
            $postId,
            $postType,
            $title,
            $content,
            self::featuredImageIdFromRequest($postId),
            self::imageIdsFromRequest(),
            ManualInputOutline::htmlForPost($postId, $postType, $title, $acf)
        );
    }

    public static function fromRequest(int $postId, string $postType): self
    {
        $title = isset($_POST['post_title']) ? (string) wp_unslash($_POST['post_title']) : '';
        $content = isset($_POST['content']) ? (string) wp_unslash($_POST['content']) : '';

        if ($title === '' && $postId > 0) {
            $title = (string) get_post_field('post_title', $postId);
        }

        if ($content === '' && $postId > 0) {
            $content = (string) get_post_field('post_content', $postId);
        }

        $imageIds = self::imageIdsFromRequest();
        if (isset($_POST['image_ids']) && is_array($_POST['image_ids'])) {
            foreach ($_POST['image_ids'] as $imageId) {
                $imageIds[] = (int) $imageId;
            }
        }

        $acf = isset($_POST['acf']) && is_array($_POST['acf']) ? $_POST['acf'] : null;

        return new self(
            $postId,
            $postType,
            $title,
            $content,
            self::featuredImageIdFromRequest($postId),
            array_values(array_unique(array_filter($imageIds))),
            ManualInputOutline::htmlForPost($postId, $postType, $title, $acf)
        );
    }

    public static function fromRest(object $preparedPost, \WP_REST_Request $request): self
    {
        $postId = isset($preparedPost->ID) ? (int) $preparedPost->ID : (int) $request->get_param('id');
        $postType = isset($preparedPost->post_type) ? (string) $preparedPost->post_type : (string) $request->get_param('type');
        $title = isset($preparedPost->post_title) ? (string) $preparedPost->post_title : '';
        $content = isset($preparedPost->post_content) ? (string) $preparedPost->post_content : '';

        if ($title === '' && $postId > 0) {
            $title = (string) get_post_field('post_title', $postId);
        }

        if ($content === '' && $postId > 0) {
            $content = (string) get_post_field('post_content', $postId);
        }

        $featured = 0;
        if (isset($preparedPost->featured_media)) {
            $featured = (int) $preparedPost->featured_media;
        } elseif ($request->get_param('featured_media') !== null) {
            $featured = (int) $request->get_param('featured_media');
        } elseif ($postId > 0) {
            $featured = (int) get_post_thumbnail_id($postId);
        }

        $imageIds = $featured > 0 ? [$featured] : [];

        return new self(
            $postId,
            $postType,
            $title,
            $content,
            $featured,
            $imageIds,
            ManualInputOutline::htmlForPost($postId, $postType, $title)
        );
    }

    public function normalizedTitle(): string
    {
        $title = html_entity_decode(wp_strip_all_tags($this->title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? $title);

        $autoDraft = __('Auto Draft');
        if ($title === '' || strcasecmp($title, 'Auto Draft') === 0 || $title === $autoDraft) {
            return '';
        }

        return $title;
    }

    private static function featuredImageIdFromRequest(int $postId): int
    {
        if (isset($_POST['_thumbnail_id'])) {
            $thumbnailId = (int) $_POST['_thumbnail_id'];
            return $thumbnailId > 0 ? $thumbnailId : 0;
        }

        if ($postId > 0) {
            return (int) get_post_thumbnail_id($postId);
        }

        return 0;
    }

    /**
     * @return array<int, int>
     */
    private static function imageIdsFromRequest(): array
    {
        $ids = [];

        if (isset($_POST['_thumbnail_id'])) {
            $thumbnailId = (int) $_POST['_thumbnail_id'];
            if ($thumbnailId > 0) {
                $ids[] = $thumbnailId;
            }
        }

        if (isset($_POST['acf']) && is_array($_POST['acf'])) {
            $ids = array_merge($ids, self::collectImageIds($_POST['acf']));
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * @param mixed $value
     * @return array<int, int>
     */
    private static function collectImageIds(mixed $value): array
    {
        $ids = [];

        if (is_array($value)) {
            if (isset($value['id']) && is_numeric($value['id'])) {
                $ids[] = (int) $value['id'];
            }

            foreach ($value as $child) {
                $ids = array_merge($ids, self::collectImageIds($child));
            }

            return $ids;
        }

        if (is_numeric($value)) {
            $id = (int) $value;
            if ($id > 0 && wp_attachment_is_image($id)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
