<?php
/**
 * Plugin Name: Säter Events Filtering
 * Description: Handles category and date filtering for the events archive. Works for post types "event" and "events" and the evenemang archive. Category counts use upcoming totals when no range is set; with a range (URL or date fields), counts use overlap on start_datum/slut_datum. A script refetches counts when date pickers change so Filtrera is not required to see them. Filters by evenemangskategorier taxonomy and start_datum/slut_datum for date range.
 *
 * Where event dates are stored:
 * - Post type: events (archive slug: evenemang). ACF group "Evenemang" (local_sater-custom-types/source/acf-export/evenemang.json).
 * - Meta keys: start_datum, slut_datum in wp_postmeta. Format: Y-m-d H:i (e.g. 2026-02-05 13:00).
 * - Single-event display: theme Singular controller + views/v3/templates/single.blade.php (Startdatum/Slutdatum cards).
 * Version: 1.3.0
 * Author: Jovica Bumbulovic
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Sater_Events_Filtering
{
    /** @var array<int, string> Cache for resolveCoreAttachmentAlt (WP alt / caption / title). */
    private static array $attachmentAltCoreCache = [];

    public function __construct()
    {
        // Ensure Municipio archive filters are visible on event archive
        add_filter('Municipio/Archive/showFilter', [$this, 'forceShowFilters'], 20);
        // Force events archive to use the same "cards" design as /nyheter.
        add_filter('Municipio/Template/events/archive/viewData', [$this, 'forceEventsArchiveCardsViewData'], 20, 2);
        add_filter('Municipio/Template/event/archive/viewData', [$this, 'forceEventsArchiveCardsViewData'], 20, 2);

        // Ensure archive dates for events use the event meta field (so cards show correct dates).
        add_filter('theme_mod_archive_events_date_field', static fn() => 'start_datum', 20);
        add_filter('theme_mod_archive_event_date_field', static fn() => 'start_datum', 20);

        // Parse event datetime meta in site timezone to avoid DST/UTC shifts (eg. +2h).
        add_filter('Municipio/DecoratePostObject', [$this, 'decoratePostObjectForEventsTimezone'], 20, 1);
        add_filter('sater_events_event_datetime_to_timestamp', [$this, 'eventDatetimeToTimestamp'], 20, 2);

        // Fallback featured image for events if editors forget to set one.
        // Needed for modules that call get_post_thumbnail_id() directly (e.g. "Latest events").
        add_filter('post_thumbnail_id', [$this, 'fallbackFeaturedImageId'], 10, 2);
        add_filter('post_thumbnail_html', [$this, 'fallbackFeaturedImageHtml'], 10, 5);
        add_filter('post_thumbnail_url', [$this, 'fallbackFeaturedImageUrl'], 10, 2);
        add_filter('Municipio/Helper/Post/postObject', [$this, 'injectPlaceholderImagesIntoMunicipioPostObject'], 10, 1);

        add_filter('query_vars', [$this, 'registerQueryVar']);
        // Strip empty date vars before Municipio Archive::onlyUpcomingEvents (20) uses isset(from/to).
        add_filter('request', [$this, 'stripInvalidDateVarsFromRequest'], 5);
        // Sync post_type on $query->query for theme code that compares $query->query['post_type'] == 'events'.
        add_action('pre_get_posts', [$this, 'syncEventsPostTypeQueryArray'], 15);
        // Run after Municipio Archive::onlyUpcomingEvents (20) so our meta_query wins and filtering works
        add_action('pre_get_posts', [$this, 'handleEventDateFiltering'], 999);
        add_action('pre_get_posts', [$this, 'applyFilter'], 999);
        add_filter('Municipio/archive/tax_query', [$this, 'mergeTaxQuery'], 99, 2);
        add_filter('Municipio/archive/date_filter', [$this, 'skipEventDateFilter'], 10, 3);
        // Strip theme's post_date condition from WHERE when we're filtering events by start_datum/slut_datum (runs after theme's posts_where priority 10)
        add_filter('posts_where', [$this, 'stripPostDateForEventsQuery'], 11, 2);

        // Show all evenemangskategorier terms in archive filter (Municipio uses hide_empty true).
        add_filter('Municipio/Controller/Archive/getTaxonomies', [$this, 'includeEmptyEvenemangskategorierTerms'], 10, 1);

        add_action('save_post', [$this, 'bustUpcomingTaxCountCache'], 10, 2);
        add_action('before_delete_post', [$this, 'bustUpcomingTaxCountCacheOnDelete']);

        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueTaxCountRefreshScript'], 20);

        // Must use Municipio/viewPaths (not Municipio/blade/view_paths): the latter is merged
        // AFTER theme paths, so theme post-grid.blade.php always wins and our override never loads.
        add_filter('Municipio/viewPaths', [$this, 'prependMunicipioViewPaths'], 1);
        // render_blade_view() builds paths as [ ComponentLibrary internal, ...Municipio paths ].
        // CL wins first match for Card.components.date; prepend our views/v3 first.
        add_filter('ComponentLibrary/ViewPaths', [$this, 'prependComponentLibraryViewPathsFirst'], 1);
        add_action('admin_menu', [$this, 'registerPlaceholderSettingsPage']);
        add_action('admin_init', [$this, 'registerPlaceholderSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueuePlaceholderSettingsAssets']);

        add_filter('sater_events_post_grid_block_image_alt', [$this, 'filterPostGridBlockImageAlt'], 10, 2);

        // Expose placeholder URL/alt for other templates (e.g. Modularity Latest News).
        add_filter('sater_events_placeholder_image_url', [$this, 'filterPlaceholderImageUrl'], 10, 1);
        add_filter('sater_events_placeholder_image_alt', [$this, 'filterPlaceholderImageAlt'], 10, 2);
    }

    public const PLACEHOLDER_ATTACHMENT_OPTION = 'sater_events_placeholder_attachment_id';

    public function filterPlaceholderImageUrl($url): string
    {
        $resolved = $this->placeholderImageUrl();
        return $resolved !== '' ? $resolved : (string) $url;
    }

    public function filterPlaceholderImageAlt($alt, $postId): string
    {
        $pid = is_numeric($postId) ? (int) $postId : 0;
        $resolved = $pid > 0 ? $this->placeholderAltForEvent($pid) : '';
        return $resolved !== '' ? $resolved : (string) $alt;
    }

    /**
     * Some templates/modules use get_post_thumbnail_id() + wp_get_attachment_image_src()
     * and bypass Municipio post objects and our URL/HTML fallbacks. For event posts, return
     * the configured placeholder attachment ID when no featured image exists.
     *
     * @param int|false $thumbnailId Resolved thumbnail attachment ID.
     * @param int       $postId      Post ID.
     * @return int|false
     */
    public function fallbackFeaturedImageId($thumbnailId, $post)
    {
        $postId = 0;
        if ($post instanceof \WP_Post) {
            $postId = (int) $post->ID;
        } elseif (is_numeric($post)) {
            $postId = (int) $post;
        }

        // Keep default behavior if a thumbnail exists or this isn't an event post.
        if (!empty($thumbnailId) || $postId <= 0 || !$this->isEventPost($postId)) {
            return $thumbnailId;
        }

        $attId = (int) get_option(self::PLACEHOLDER_ATTACHMENT_OPTION, 0);
        if ($attId > 0 && wp_attachment_is_image($attId)) {
            return $attId;
        }

        return $thumbnailId;
    }

    /**
     * Absolute path to the versioned view root that mirrors the theme's views/v3 layout.
     * Must end without a trailing slash so comparisons work.
     */
    private function pluginViewV3Root(): string
    {
        return rtrim(plugin_dir_path(__FILE__) . 'views/v3', '/\\');
    }

    /**
     * Make Blade resolve our post-grid override before the theme's copy.
     *
     * Municipio/Template::renderView passes $this->viewPaths to BladeService::makeView,
     * which loops the array and calls prependLocation (= array_unshift) on each entry.
     * Because array_unshift makes the last call win, our path must be the LAST element
     * in the returned array so it gets prepended last and sits first in the finder.
     *
     * The path must be views/v3 (not views) to match the theme's depth so that
     * "partials.post.post-grid" resolves to partials/post/post-grid.blade.php correctly.
     *
     * @param array<int, string> $paths From Municipio\Helper\Template::getViewPaths().
     * @return array<int, string>
     */
    public function prependMunicipioViewPaths(array $paths): array
    {
        $root = $this->pluginViewV3Root();
        if ($root === '' || !is_dir($root)) {
            return $paths;
        }

        // Remove any existing copy of our root to avoid duplicates.
        $filtered = array_values(array_filter($paths, static function (string $p) use ($root): bool {
            return rtrim($p, '/\\') !== $root;
        }));

        // Append LAST so prependLocation puts it FIRST in the Blade finder's path list.
        return array_merge($filtered, [$root]);
    }

    /**
     * Put this plugin's views/v3 first so overrides (e.g. Card.components.date) beat vendor ComponentLibrary.
     *
     * @param array<int, string> $viewPaths Paths passed to ComponentLibrary\Init after merge.
     * @return array<int, string>
     */
    public function prependComponentLibraryViewPathsFirst(array $viewPaths): array
    {
        $root = $this->pluginViewV3Root();
        if ($root === '' || !is_dir($root)) {
            return $viewPaths;
        }

        $filtered = array_values(array_filter(
            $viewPaths,
            static function ($path) use ($root): bool {
                return rtrim((string) $path, '/\\') !== $root;
            }
        ));

        array_unshift($filtered, $root);

        return $filtered;
    }

    public function registerPlaceholderSettingsPage(): void
    {
        add_options_page(
            __('Säter evenemang', 'sater-events-filtering'),
            __('Säter evenemang', 'sater-events-filtering'),
            'manage_options',
            'sater-events-placeholder',
            [$this, 'renderPlaceholderSettingsPage']
        );
    }

    public function registerPlaceholderSettings(): void
    {
        register_setting(
            'sater_events_settings_group',
            self::PLACEHOLDER_ATTACHMENT_OPTION,
            [
                'type'              => 'integer',
                'sanitize_callback' => [$this, 'sanitizePlaceholderAttachmentId'],
                'default'           => 0,
            ]
        );

        add_settings_section(
            'sater_events_placeholder_section',
            __('Standardbild för evenemang', 'sater-events-filtering'),
            static function (): void {
                echo '<p>' . esc_html__(
                    'Välj en bild från mediabiblioteket som visas när ett evenemang saknar utvald bild. Om inget väljs används den inbyggda reservbilden.',
                    'sater-events-filtering'
                ) . '</p>';
            },
            'sater-events-placeholder'
        );

        add_settings_field(
            'sater_events_placeholder_attachment_field',
            __('Platshållarbild', 'sater-events-filtering'),
            [$this, 'renderPlaceholderAttachmentField'],
            'sater-events-placeholder',
            'sater_events_placeholder_section'
        );
    }

    public function sanitizePlaceholderAttachmentId($value): int
    {
        $id = max(0, (int) $value);
        if ($id === 0) {
            return 0;
        }
        if (!wp_attachment_is_image($id)) {
            return 0;
        }

        return $id;
    }

    public function renderPlaceholderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Säter evenemang', 'sater-events-filtering'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('sater_events_settings_group');
                do_settings_sections('sater-events-placeholder');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function renderPlaceholderAttachmentField(): void
    {
        $id  = (int) get_option(self::PLACEHOLDER_ATTACHMENT_OPTION, 0);
        $url = ($id > 0) ? wp_get_attachment_image_url($id, 'medium') : '';
        ?>
        <input type="hidden" name="<?php echo esc_attr(self::PLACEHOLDER_ATTACHMENT_OPTION); ?>" id="sater_events_placeholder_attachment_id" value="<?php echo esc_attr((string) $id); ?>" />
        <p>
            <button type="button" class="button" id="sater_events_placeholder_select"><?php esc_html_e('Välj bild', 'sater-events-filtering'); ?></button>
            <button type="button" class="button" id="sater_events_placeholder_clear"><?php esc_html_e('Ta bort vald bild', 'sater-events-filtering'); ?></button>
        </p>
        <div id="sater_events_placeholder_preview" style="margin-top:8px;">
            <?php
            if ($url) {
                echo '<img src="' . esc_url($url) . '" alt="" style="max-width:240px;height:auto;border:1px solid #ccd0d4;padding:4px;background:#fff;" />';
            }
            ?>
        </div>
        <script>
        (function () {
            document.getElementById('sater_events_placeholder_select')?.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof wp === 'undefined' || !wp.media) { return; }
                var frame = wp.media({ title: <?php echo wp_json_encode(__('Välj platshållarbild', 'sater-events-filtering')); ?>, button: { text: <?php echo wp_json_encode(__('Använd bild', 'sater-events-filtering')); ?> }, library: { type: 'image' }, multiple: false });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    document.getElementById('sater_events_placeholder_attachment_id').value = att.id;
                    var el = document.getElementById('sater_events_placeholder_preview');
                    if (el && att.sizes && att.sizes.medium && att.sizes.medium.url) {
                        el.innerHTML = '<img src="' + att.sizes.medium.url + '" alt="" style="max-width:240px;height:auto;border:1px solid #ccd0d4;padding:4px;background:#fff;" />';
                    } else if (el && att.url) {
                        el.innerHTML = '<img src="' + att.url + '" alt="" style="max-width:240px;height:auto;border:1px solid #ccd0d4;padding:4px;background:#fff;" />';
                    }
                });
                frame.open();
            });
            document.getElementById('sater_events_placeholder_clear')?.addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('sater_events_placeholder_attachment_id').value = '0';
                var el = document.getElementById('sater_events_placeholder_preview');
                if (el) { el.innerHTML = ''; }
            });
        })();
        </script>
        <?php
    }

    public function enqueuePlaceholderSettingsAssets(string $hook): void
    {
        if ($hook !== 'settings_page_sater-events-placeholder') {
            return;
        }
        wp_enqueue_media();
    }

    private function placeholderImageUrl(): string
    {
        $id = (int) get_option(self::PLACEHOLDER_ATTACHMENT_OPTION, 0);
        if ($id > 0) {
            $url = wp_get_attachment_image_url($id, 'full');
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        // If not configured (or invalid), do not force a fallback image.
        return '';
    }

    /**
     * Core WP alt for an attachment: Alternative text meta, then wp_prepare_attachment_for_js, caption, description, title.
     */
    private function resolveCoreAttachmentAlt(int $attachmentId): string
    {
        if ($attachmentId <= 0 || !wp_attachment_is_image($attachmentId)) {
            return '';
        }
        if (array_key_exists($attachmentId, self::$attachmentAltCoreCache)) {
            return self::$attachmentAltCoreCache[$attachmentId];
        }

        $metaAlt = get_post_meta($attachmentId, '_wp_attachment_image_alt', true);
        $metaAlt = is_string($metaAlt) ? trim(wp_strip_all_tags($metaAlt)) : '';
        if ($metaAlt !== '') {
            self::$attachmentAltCoreCache[$attachmentId] = $metaAlt;

            return $metaAlt;
        }

        if (function_exists('wp_prepare_attachment_for_js')) {
            $prepared = wp_prepare_attachment_for_js($attachmentId);
            if (is_array($prepared) && isset($prepared['alt']) && is_string($prepared['alt'])) {
                $a = trim(wp_strip_all_tags($prepared['alt']));
                if ($a !== '') {
                    self::$attachmentAltCoreCache[$attachmentId] = $a;

                    return $a;
                }
            }
        }

        $post = get_post($attachmentId);
        if ($post instanceof \WP_Post) {
            foreach (['post_excerpt', 'post_content', 'post_title'] as $field) {
                $chunk = isset($post->{$field}) ? trim(wp_strip_all_tags((string) $post->{$field})) : '';
                if ($chunk !== '') {
                    self::$attachmentAltCoreCache[$attachmentId] = $chunk;

                    return $chunk;
                }
            }
        }

        self::$attachmentAltCoreCache[$attachmentId] = '';

        return '';
    }

    /**
     * Placeholder image alt: optional filter, then core WP fields.
     * Filter `sater_events_placeholder_attachment_alt` (string $current, int $attachmentId).
     */
    private function getPlaceholderAttachmentAltText(int $attachmentId): string
    {
        if ($attachmentId <= 0 || !wp_attachment_is_image($attachmentId)) {
            return '';
        }

        $fromFilter = (string) apply_filters('sater_events_placeholder_attachment_alt', '', $attachmentId);
        $fromFilter = trim(wp_strip_all_tags($fromFilter));
        if ($fromFilter !== '') {
            return $fromFilter;
        }

        return $this->resolveCoreAttachmentAlt($attachmentId);
    }

    private function placeholderAltForEvent(int $postId): string
    {
        $title = get_the_title($postId);
        $title = is_string($title) ? trim($title) : '';

        $attId = (int) get_option(self::PLACEHOLDER_ATTACHMENT_OPTION, 0);
        if ($attId > 0 && wp_attachment_is_image($attId)) {
            $imgAlt = $this->getPlaceholderAttachmentAltText($attId);
            if ($imgAlt !== '') {
                return $imgAlt;
            }
        }

        if ($title !== '') {
            return $title . ' - Logotyp Säters kommun';
        }

        return 'Logotyp Säters kommun';
    }

    /**
     * @param mixed            $ignored Default from apply_filters (unused).
     * @param object           $post    Municipio post object in archive grid.
     * @return string Alt for Block imageAlt (always a non-empty string when possible).
     */
    public function filterPostGridBlockImageAlt($ignored, object $post): string
    {
        $postTitle = isset($post->postTitle) ? trim((string) $post->postTitle) : '';
        $postId     = isset($post->id) ? (int) $post->id : 0;

        // Featured image uses ImageContract; Block does not read image['alt'], only imageAlt (see component-library Block).
        if ($post->imageContract ?? null) {
            $thumbId = $postId > 0 ? (int) get_post_thumbnail_id($postId) : 0;
            if ($thumbId > 0) {
                $alt = $this->resolveCoreAttachmentAlt($thumbId);
                if ($alt !== '') {
                    return $alt;
                }
            }

            return $postTitle !== '' ? $postTitle : 'Evenemang';
        }

        $thumb = $post->images['thumbnail16:9'] ?? ($post->images['thumbnail_16:9'] ?? null);
        if ($thumb === false || $thumb === null) {
            return $postTitle !== '' ? $postTitle : 'Logotyp Säters kommun';
        }

        $arr = is_array($thumb) ? $thumb : (array) $thumb;
        $raw = $arr['alt'] ?? '';
        $alt = is_string($raw) ? trim(wp_strip_all_tags($raw)) : '';
        if ($alt !== '') {
            return $alt;
        }

        return $postTitle !== '' ? $postTitle : 'Logotyp Säters kommun';
    }

    /**
     * @param int|\WP_Post|null $post Post ID or object.
     */
    private function isEventPost($post): bool
    {
        $postId = 0;
        if ($post instanceof \WP_Post) {
            $postId = (int) $post->ID;
        } elseif (is_numeric($post)) {
            $postId = (int) $post;
        }

        if ($postId <= 0) {
            return false;
        }

        $pt = get_post_type($postId);
        return $pt === 'events' || $pt === 'event';
    }

    /**
     * Provide a placeholder thumbnail URL for events without featured images.
     *
     * @param string|false      $url  Resolved URL.
     * @param int|\WP_Post|null $post Post ID or object.
     * @return string|false
     */
    public function fallbackFeaturedImageUrl($url, $post)
    {
        if (is_admin()) {
            return $url;
        }
        if (!empty($url)) {
            return $url;
        }
        if (!$this->isEventPost($post)) {
            return $url;
        }
        if (has_post_thumbnail($post)) {
            return $url;
        }

        return $this->placeholderImageUrl();
    }

    /**
     * Provide placeholder thumbnail HTML for events without featured images.
     *
     * @param string            $html              Existing HTML.
     * @param int|\WP_Post      $post              Post ID or object.
     * @param string|int[]      $size              Requested size.
     * @param string|string[]   $attr              Attributes.
     * @param int              $attachmentId       Attachment ID.
     * @return string
     */
    public function fallbackFeaturedImageHtml($html, $post, $size, $attr, $attachmentId): string
    {
        if (is_admin()) {
            return $html;
        }
        if (!empty($html)) {
            return $html;
        }
        if (!$this->isEventPost($post)) {
            return $html;
        }
        if (has_post_thumbnail($post) || !empty($attachmentId)) {
            return $html;
        }

        $classes = ['wp-post-image', 'sater-event-placeholder'];
        if (is_string($attr) && $attr !== '') {
            // Ignore; WP core uses array for attrs.
        } elseif (is_array($attr) && !empty($attr['class'])) {
            $classes[] = (string) $attr['class'];
        }

        $alt = '';
        if (is_array($attr) && isset($attr['alt'])) {
            $alt = (string) $attr['alt'];
        }
        if ($alt === '') {
            $postId = ($post instanceof \WP_Post) ? (int) $post->ID : (int) $post;
            $alt     = $postId > 0 ? $this->placeholderAltForEvent($postId) : 'Logotyp Säters kommun';
        }

        $classAttr = esc_attr(trim(implode(' ', array_filter($classes))));
        $src       = esc_url($this->placeholderImageUrl());
        $altAttr   = esc_attr($alt);

        return '<img src="' . $src . '" class="' . $classAttr . '" alt="' . $altAttr . '" loading="lazy" decoding="async" />';
    }

    /**
     * Municipio archive cards use \Municipio\Helper\Post postObjects and their precomputed $post->images array.
     * If an event lacks a featured image, that array contains false values, so we inject a placeholder there.
     *
     * @param object $postObject Municipio post object.
     * @return object
     */
    public function injectPlaceholderImagesIntoMunicipioPostObject(object $postObject): object
    {
        if (is_admin() || empty($postObject->ID)) {
            return $postObject;
        }
        if (!$this->isEventPost((int) $postObject->ID)) {
            return $postObject;
        }
        if (has_post_thumbnail((int) $postObject->ID)) {
            return $postObject;
        }

        $altText     = $this->placeholderAltForEvent((int) $postObject->ID);
        $placeholder = [
            'src'         => $this->placeholderImageUrl(),
            'alt'         => $altText,
            'title'       => null,
            'caption'     => null,
            'description' => null,
            'byline'      => null,
        ];

        if (!isset($postObject->images) || !is_array($postObject->images)) {
            $postObject->images = [];
        }

        $keys = [
            'thumbnail_16:9',
            'thumbnail_4:3',
            'thumbnail_1:1',
            'thumbnail_3:4',
            'thumbnail_12:16',
            'featuredImage',
            // Municipio templates often use camelCased versions.
            'thumbnail16:9',
            'thumbnail4:3',
            'thumbnail1:1',
            'thumbnail3:4',
            'thumbnail12:16',
        ];

        foreach ($keys as $key) {
            if (empty($postObject->images[$key])) {
                $postObject->images[$key] = $placeholder;
            }
        }

        // Backwards compatible properties used in some templates.
        if (empty($postObject->thumbnail)) {
            $postObject->thumbnail = $postObject->images['thumbnail_16:9'] ?? $placeholder;
        }
        if (empty($postObject->thumbnail_tall)) {
            $postObject->thumbnail_tall = $postObject->images['thumbnail_4:3'] ?? $placeholder;
        }
        if (empty($postObject->thumbnail_square)) {
            $postObject->thumbnail_square = $postObject->images['thumbnail_1:1'] ?? $placeholder;
        }
        if (empty($postObject->featuredImage)) {
            $postObject->featuredImage = $postObject->images['featuredImage'] ?? $placeholder;
        }

        return $postObject;
    }

    public function forceShowFilters($enabled)
    {
        if (!is_admin() && $this->isEventsArchiveView()) {
            return true;
        }
        return $enabled;
    }

    /**
     * True on the events CPT archive (covers edge cases where is_post_type_archive is unreliable).
     */
    private function isEventsArchiveView(): bool
    {
        if (is_post_type_archive('event') || is_post_type_archive('events')) {
            return true;
        }
        $obj = get_queried_object();
        if ($obj instanceof \WP_Post_Type && ($obj->name === 'events' || $obj->name === 'event')) {
            return true;
        }

        return false;
    }

    /**
     * Municipio chooses the archive partial based on `$template` in view data.
     * For events we want the same output as Nyheter: `post-newsitem` (includes excerpt).
     *
     * @param array $viewData Current view data passed to Blade.
     * @param string $template Current template identifier (from controller/customizer).
     * @return array
     */
    public function forceEventsArchiveCardsViewData(array $viewData, string $template): array
    {
        if (is_admin() || !$this->isEventsArchiveView()) {
            return $viewData;
        }

        $viewData['template'] = 'cards';

        return $viewData;
    }

    /**
     * Convert an event datetime string (Y-m-d H:i) to a timestamp in site timezone.
     *
     * @param int|null $current Current value (usually null).
     * @param string $value Datetime string from meta (e.g. "2026-05-22 07:00").
     */
    public function eventDatetimeToTimestamp($current, string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return is_numeric($current) ? (int) $current : null;
        }

        if (!function_exists('wp_timezone')) {
            return strtotime($value) ?: (is_numeric($current) ? (int) $current : null);
        }

        $tz = wp_timezone();

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $fmt) {
            $dt = \DateTimeImmutable::createFromFormat($fmt, $value, $tz);
            if ($dt instanceof \DateTimeImmutable) {
                $errors = \DateTimeImmutable::getLastErrors();
                $hasErrors = is_array($errors) && (!empty($errors['warning_count']) || !empty($errors['error_count']));
                if (!$hasErrors) {
                    return $dt->getTimestamp();
                }
            }
        }

        // Fallback to strtotime in PHP default timezone.
        return strtotime($value) ?: (is_numeric($current) ? (int) $current : null);
    }

    /**
     * Override Municipio archive timestamp for events by parsing start_datum as site-local time.
     * Prevents DST / timezone shifts when formatting with wp_date().
     */
    public function decoratePostObjectForEventsTimezone($postObject)
    {
        if (!$postObject || !is_object($postObject) || !method_exists($postObject, 'getId') || !method_exists($postObject, 'getPostType')) {
            return $postObject;
        }

        $pt = (string) $postObject->getPostType();
        if ($pt !== 'events' && $pt !== 'event') {
            return $postObject;
        }

        $base = $postObject;

        return new class($base, $this) implements \Municipio\PostObject\PostObjectInterface {
            public function __construct(
                private \Municipio\PostObject\PostObjectInterface $inner,
                private \Sater_Events_Filtering $plugin
            ) {
            }

            public function getArchiveDateTimestamp(): ?int
            {
                $raw = get_post_meta($this->inner->getId(), 'start_datum', true);
                $raw = is_string($raw) ? $raw : '';
                $ts = apply_filters('sater_events_event_datetime_to_timestamp', null, $raw);
                if (is_numeric($ts) && (int) $ts > 0) {
                    return (int) $ts;
                }

                return $this->inner->getArchiveDateTimestamp();
            }

            // Delegate everything else
            public function getId(): int { return $this->inner->getId(); }
            public function getTitle(): string { return $this->inner->getTitle(); }
            public function getPermalink(): string { return $this->inner->getPermalink(); }
            public function getCommentCount(): int { return $this->inner->getCommentCount(); }
            public function getPostType(): string { return $this->inner->getPostType(); }
            public function getIcon(): ?\Municipio\PostObject\Icon\IconInterface { return $this->inner->getIcon(); }
            public function getBlogId(): int { return $this->inner->getBlogId(); }
            public function getPublishedTime(bool $gmt = false): int { return $this->inner->getPublishedTime($gmt); }
            public function getModifiedTime(bool $gmt = false): int { return $this->inner->getModifiedTime($gmt); }
            public function getArchiveDateFormat(): string { return $this->inner->getArchiveDateFormat(); }
            public function getSchemaProperty(string $property): mixed { return $this->inner->getSchemaProperty($property); }
            public function getSchema(): \Municipio\Schema\BaseType { return $this->inner->getSchema(); }
            public function getTerms(array $taxonomies): array { return $this->inner->getTerms($taxonomies); }
            public function __get(string $key): mixed { return $this->inner->__get($key); }
        };
    }

    /**
     * @param array<int, array<string, mixed>> $taxonomyObjects Municipio filter rows.
     */
    private function taxonomyObjectsIncludeEvenemangskategorier(array $taxonomyObjects): bool
    {
        foreach ($taxonomyObjects as $object) {
            if (($object['attributeList']['name'] ?? '') === 'evenemangskategorier') {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the given post_type value is event(s) — supports 'event', 'events', and evenemang (archive slug for events).
     *
     * @param string|array $postType Query post_type (string or array)
     * @return bool
     */
    private function isEventPostType($postType)
    {
        if ($postType === 'event' || $postType === 'events') {
            return true;
        }
        if (is_array($postType)) {
            return in_array('event', $postType) || in_array('events', $postType);
        }
        return false;
    }

    /**
     * Replace or inject archive multiselect options so evenemangskategorier includes zero-count terms.
     *
     * @param array $taxonomyObjects Municipio archive filter definitions.
     * @return array
     */
    public function includeEmptyEvenemangskategorierTerms($taxonomyObjects)
    {
        if (!is_array($taxonomyObjects)) {
            return $taxonomyObjects;
        }
        if (is_admin()) {
            return $taxonomyObjects;
        }
        if (!$this->isEventsArchiveView() && !$this->taxonomyObjectsIncludeEvenemangskategorier($taxonomyObjects)) {
            return $taxonomyObjects;
        }

        $taxonomyName = 'evenemangskategorier';
        $taxonomy     = get_taxonomy($taxonomyName);
        if (!$taxonomy instanceof \WP_Taxonomy) {
            return $taxonomyObjects;
        }

        $options = $this->getEvenemangskategorierTermOptions($taxonomy);
        if ($options === []) {
            return $taxonomyObjects;
        }

        $foundIndex = null;
        foreach ($taxonomyObjects as $index => $object) {
            if (($object['attributeList']['name'] ?? '') === $taxonomyName) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== null) {
            $taxonomyObjects[$foundIndex]['options'] = $options;
            return $taxonomyObjects;
        }

        if (!$this->evenemangskategorierEnabledForEventsArchive()) {
            return $taxonomyObjects;
        }

        $taxonomyObjects[] = $this->buildEvenemangskategorierFilterBlock($taxonomy, $options);

        return $taxonomyObjects;
    }

    /**
     * @return array<string, string> slug => label (same shape as Municipio getTaxonomyFilters).
     */
    private function getEvenemangskategorierTermOptions(\WP_Taxonomy $taxonomy): array
    {
        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy->name,
                'hide_empty' => false,
            ]
        );

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        $countsBySlug = $this->getEventCountsByTermSlugForArchiveDropdown();

        $options = [];
        foreach ($terms as $option) {
            if (empty($option->name)) {
                continue;
            }
            $value = $option->slug;
            $count = (int) ($countsBySlug[$value] ?? 0);
            $label = ucfirst($option->name) . ' (' . $count . ')';
            $value = apply_filters('Municipio/Archive/getTaxonomyFilters/option/value', $value, $option, $taxonomy);
            $label = apply_filters('Municipio/Archive/getTaxonomyFilters/option/label', $label, $option, $taxonomy);
            $options[$value] = $label;
        }

        return $options;
    }

    /**
     * Read from/to from request (same sources as archive filter).
     *
     * @return array{from: ?string, to: ?string} Y-m-d or null
     */
    private function getSelectedArchiveDateBounds(): array
    {
        $fromRaw = isset($_GET['from']) ? (string) $_GET['from'] : '';
        $toRaw   = isset($_GET['to']) ? (string) $_GET['to'] : '';
        if ($fromRaw === '' && function_exists('get_query_var')) {
            $fromRaw = (string) get_query_var('from', '');
        }
        if ($toRaw === '' && function_exists('get_query_var')) {
            $toRaw = (string) get_query_var('to', '');
        }

        return $this->parseDateBoundsFromStrings($fromRaw, $toRaw);
    }

    /**
     * @return array{from: ?string, to: ?string} Y-m-d or null
     */
    private function parseDateBoundsFromStrings(string $fromRaw, string $toRaw): array
    {
        $fromRaw = trim(sanitize_text_field($fromRaw));
        $toRaw   = trim(sanitize_text_field($toRaw));

        $from = ($fromRaw !== '' && strtotime($fromRaw)) ? date('Y-m-d', strtotime($fromRaw)) : null;
        $to   = ($toRaw !== '' && strtotime($toRaw)) ? date('Y-m-d', strtotime($toRaw)) : null;

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array<string, int> term slug => count
     */
    private function getEventCountsByTermSlugBounded(?string $from, ?string $to): array
    {
        if ($from === null && $to === null) {
            return $this->getUpcomingEventCountsByTermSlugCached();
        }

        return $this->getRangeOverlapEventCountsByTermSlug($from, $to);
    }

    /**
     * Per-term counts for the category dropdown: upcoming-only without dates in URL;
     * with from/to, counts events overlapping that range (same idea as handleEventDateFiltering).
     *
     * @return array<string, int> term slug => count
     */
    private function getEventCountsByTermSlugForArchiveDropdown(): array
    {
        $bounds = $this->getSelectedArchiveDateBounds();

        return $this->getEventCountsByTermSlugBounded($bounds['from'], $bounds['to']);
    }

    public function registerRestRoutes(): void
    {
        register_rest_route(
            'sater-events/v1',
            '/taxonomy-term-counts',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'restTaxonomyTermCounts'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'from' => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'to'   => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request Request.
     * @return \WP_REST_Response|\WP_Error
     */
    public function restTaxonomyTermCounts(\WP_REST_Request $request)
    {
        $bounds = $this->parseDateBoundsFromStrings(
            (string) $request->get_param('from'),
            (string) $request->get_param('to')
        );
        $counts = $this->getEventCountsByTermSlugBounded($bounds['from'], $bounds['to']);

        return new \WP_REST_Response(['counts' => $counts], 200);
    }

    public function enqueueTaxCountRefreshScript(): void
    {
        if (is_admin() || !$this->isEventsArchiveView()) {
            return;
        }
        $path = plugin_dir_path(__FILE__) . 'assets/sater-events-tax-counts.js';
        if (!is_readable($path)) {
            return;
        }
        wp_enqueue_script(
            'sater-events-tax-counts',
            plugins_url('assets/sater-events-tax-counts.js', __FILE__),
            [],
            '1.2.3',
            true
        );
        wp_localize_script(
            'sater-events-tax-counts',
            'saterEventsTaxCounts',
            [
                'restUrl' => rest_url('sater-events/v1/taxonomy-term-counts'),
            ]
        );
    }

    /**
     * Published events per term slug where slut_datum is today or later (site timezone).
     *
     * @return array<string, int> term slug => count
     */
    private function getUpcomingEventCountsByTermSlugCached(): array
    {
        $today = current_time('Y-m-d');
        $key   = 'sater_ev_up_tax_' . md5($today);

        $cached = get_transient($key);
        if ($cached !== false && is_array($cached)) {
            return $cached;
        }

        global $wpdb;

        $taxonomy = 'evenemangskategorier';

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names; values are prepared.
        $sql = $wpdb->prepare(
            "SELECT t.slug, COUNT(DISTINCT p.ID) AS term_count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
            INNER JOIN {$wpdb->term_taxonomy} tt
                ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s)
            INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
            INNER JOIN {$wpdb->postmeta} pm
                ON (p.ID = pm.post_id AND pm.meta_key = 'slut_datum' AND pm.meta_value >= %s)
            WHERE p.post_type IN ('events','event')
            AND p.post_status = 'publish'
            GROUP BY t.slug",
            $taxonomy,
            $today
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        $out = $this->runTermCountQuery($sql);
        set_transient($key, $out, MINUTE_IN_SECONDS * 15);

        return $out;
    }

    /**
     * Count events per term overlapping [from, to] on event dates (DATE() on meta for Y-m-d H:i values).
     *
     * @param string|null $from Y-m-d or null.
     * @param string|null $to   Y-m-d or null.
     * @return array<string, int>
     */
    private function getRangeOverlapEventCountsByTermSlug(?string $from, ?string $to): array
    {
        global $wpdb;

        $taxonomy = 'evenemangskategorier';

        if ($from !== null && $to !== null) {
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $wpdb->prepare(
                "SELECT t.slug, COUNT(DISTINCT p.ID) AS term_count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                INNER JOIN {$wpdb->term_taxonomy} tt
                    ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s)
                INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
                INNER JOIN {$wpdb->postmeta} pm_start
                    ON (p.ID = pm_start.post_id AND pm_start.meta_key = 'start_datum')
                LEFT JOIN {$wpdb->postmeta} pm_slut
                    ON (p.ID = pm_slut.post_id AND pm_slut.meta_key = 'slut_datum')
                WHERE p.post_type IN ('events','event')
                AND p.post_status = 'publish'
                AND DATE(pm_start.meta_value) <= %s
                AND (
                    pm_slut.meta_id IS NULL
                    OR pm_slut.meta_value = ''
                    OR DATE(pm_slut.meta_value) >= %s
                )
                GROUP BY t.slug",
                $taxonomy,
                $to,
                $from
            );
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            return $this->runTermCountQuery($sql);
        }

        if ($from !== null) {
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $wpdb->prepare(
                "SELECT t.slug, COUNT(DISTINCT p.ID) AS term_count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                INNER JOIN {$wpdb->term_taxonomy} tt
                    ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s)
                INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
                LEFT JOIN {$wpdb->postmeta} pm_slut
                    ON (p.ID = pm_slut.post_id AND pm_slut.meta_key = 'slut_datum')
                WHERE p.post_type IN ('events','event')
                AND p.post_status = 'publish'
                AND (
                    pm_slut.meta_id IS NULL
                    OR pm_slut.meta_value = ''
                    OR DATE(pm_slut.meta_value) >= %s
                )
                GROUP BY t.slug",
                $taxonomy,
                $from
            );
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            return $this->runTermCountQuery($sql);
        }

        if ($to !== null) {
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $wpdb->prepare(
                "SELECT t.slug, COUNT(DISTINCT p.ID) AS term_count
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id)
                INNER JOIN {$wpdb->term_taxonomy} tt
                    ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s)
                INNER JOIN {$wpdb->terms} t ON (tt.term_id = t.term_id)
                INNER JOIN {$wpdb->postmeta} pm_start
                    ON (p.ID = pm_start.post_id AND pm_start.meta_key = 'start_datum')
                WHERE p.post_type IN ('events','event')
                AND p.post_status = 'publish'
                AND DATE(pm_start.meta_value) <= %s
                GROUP BY t.slug",
                $taxonomy,
                $to
            );
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            return $this->runTermCountQuery($sql);
        }

        return [];
    }

    /**
     * @param string $sql Full SQL with prepare already applied.
     * @return array<string, int>
     */
    private function runTermCountQuery(string $sql): array
    {
        global $wpdb;
        $rows = $wpdb->get_results($sql, ARRAY_A);
        $out  = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!empty($row['slug'])) {
                    $out[(string) $row['slug']] = (int) $row['term_count'];
                }
            }
        }

        return $out;
    }

    /**
     * @param int      $postId Post ID.
     * @param \WP_Post $post   Post object.
     */
    public function bustUpcomingTaxCountCache($postId, $post): void
    {
        if (wp_is_post_revision($postId)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $pt = ($post instanceof \WP_Post) ? $post->post_type : get_post_type($postId);
        if ($pt !== 'events' && $pt !== 'event') {
            return;
        }
        delete_transient('sater_ev_up_tax_' . md5(current_time('Y-m-d')));
    }

    /**
     * @param int $postId Post ID.
     */
    public function bustUpcomingTaxCountCacheOnDelete($postId): void
    {
        $pt = get_post_type($postId);
        if ($pt === 'events' || $pt === 'event') {
            delete_transient('sater_ev_up_tax_' . md5(current_time('Y-m-d')));
        }
    }

    /**
     * When Municipio skipped the taxonomy (all terms empty with hide_empty), only inject if customizer still enables it.
     */
    private function evenemangskategorierEnabledForEventsArchive(): bool
    {
        $enabled = get_theme_mod('archive_events_enabled_filters', []);
        if (!is_array($enabled)) {
            return false;
        }
        $enabled = array_filter($enabled);
        return in_array('evenemangskategorier', $enabled, true);
    }

    /**
     * Build a Municipio-shaped filter block when the theme omitted the taxonomy row.
     *
     * @param \WP_Taxonomy          $taxonomy Taxonomy object.
     * @param array<string, string> $options  Slug => label options.
     * @return array<string, mixed>
     */
    private function buildEvenemangskategorierFilterBlock(\WP_Taxonomy $taxonomy, array $options): array
    {
        $fieldType = get_theme_mod(
            'archive_events_evenemangskategorier_filter_field_type',
            'multi'
        );
        if (!in_array($fieldType, ['multi', 'single'], true)) {
            $fieldType = 'multi';
        }

        $block = [
            'label'         => __('Select', 'municipio') . ' ' . strtolower($taxonomy->labels->singular_name),
            'required'      => false,
            'attributeList' => [
                'type' => 'text',
                'name' => $taxonomy->name,
            ],
            'fieldType' => $fieldType,
            'options'   => $options,
        ];

        if (isset($_GET[$taxonomy->name])) {
            $raw = $_GET[$taxonomy->name];
            if (is_array($raw)) {
                $block['preselected'] = array_map('sanitize_text_field', $raw);
            } else {
                $block['preselected'] = [sanitize_text_field((string) $raw)];
            }
        }

        return $block;
    }

    public function registerQueryVar(array $vars): array
    {
        $vars[] = 'evenemangskategorier';
        $vars[] = 'from';
        $vars[] = 'to';
        return $vars;
    }

    /**
     * Remove empty or invalid from/to so Municipio theme does not treat them as set (bad meta_query).
     *
     * @param array $queryVars Public query variables.
     * @return array
     */
    public function stripInvalidDateVarsFromRequest($queryVars)
    {
        if (!is_array($queryVars) || is_admin()) {
            return $queryVars;
        }
        foreach (['from', 'to'] as $key) {
            if (!isset($queryVars[$key])) {
                continue;
            }
            $v = $queryVars[$key];
            if ($v === '' || $v === null || (is_string($v) && trim($v) === '')) {
                unset($queryVars[$key]);
                continue;
            }
            if (is_string($v) && strtotime($v) === false) {
                unset($queryVars[$key]);
            }
        }
        return $queryVars;
    }

    /**
     * Theme Archive::onlyUpcomingEvents checks $query->query['post_type'] == 'events' (not get('post_type')).
     *
     * @param \WP_Query $query Main query.
     */
    public function syncEventsPostTypeQueryArray($query): void
    {
        if (is_admin() || !$query->is_main_query() || !is_archive()) {
            return;
        }
        $pt = $query->get('post_type');
        if ($pt !== 'events' && $pt !== 'event') {
            return;
        }
        if (!isset($query->query['post_type']) || $query->query['post_type'] !== $pt) {
            $query->query['post_type'] = $pt;
        }
    }

    /**
     * Handle event date filtering at the query level
     * Filters events using start_datum/slut_datum meta fields instead of post dates.
     * Runs on any query for post type "event" or "events" (evenemang) when from/to are set.
     *
     * @param WP_Query $query
     * @return void
     */
    public function handleEventDateFiltering($query)
    {
        if (is_admin()) {
            return;
        }
        $pt = $query->get('post_type');
        if (!$this->isEventPostType($pt)) {
            return;
        }

        $from = get_query_var('from', false);
        if (!$from && isset($_GET['from']) && !empty($_GET['from'])) {
            $from = sanitize_text_field($_GET['from']);
        }
        $to = get_query_var('to', false);
        if (!$to && isset($_GET['to']) && !empty($_GET['to'])) {
            $to = sanitize_text_field($_GET['to']);
        }
        if (!$from && !$to) {
            return;
        }

        $fromDate = $from ? date('Y-m-d', strtotime($from)) : null;
        $toDate = $to ? date('Y-m-d', strtotime($to)) : null;
        $query->set('meta_query', []);
        
        // Build clean meta query for event dates
        $metaQuery = ['relation' => 'AND'];
        
        if ($fromDate && $toDate) {
            // Events that overlap with the date range
            $metaQuery[] = [
                'key' => 'start_datum',
                'value' => $toDate,
                'compare' => '<=',
                'type' => 'DATE'
            ];
            $metaQuery[] = [
                'relation' => 'OR',
                [
                    'key' => 'slut_datum',
                    'value' => $fromDate,
                    'compare' => '>=',
                    'type' => 'DATE'
                ],
                [
                    'key' => 'slut_datum',
                    'compare' => 'NOT EXISTS'
                ]
            ];
        } elseif ($fromDate) {
            // Events that end on or after the from date
            $metaQuery[] = [
                'relation' => 'OR',
                [
                    'key' => 'slut_datum',
                    'value' => $fromDate,
                    'compare' => '>=',
                    'type' => 'DATE'
                ],
                [
                    'key' => 'slut_datum',
                    'compare' => 'NOT EXISTS'
                ]
            ];
        } elseif ($toDate) {
            // Events that start on or before the to date
            $metaQuery[] = [
                'key' => 'start_datum',
                'value' => $toDate,
                'compare' => '<=',
                'type' => 'DATE'
            ];
        }
        
        $query->set('meta_query', $metaQuery);
        $query->set('date_query', []);
    }

    /**
     * Strip Municipio's post_date condition from WHERE when this query is for events and has from/to.
     * Theme adds post_date range in doPostDateFiltering (posts_where priority 10); we run at 11 so we can remove it for events.
     * This fixes "Inga hittades" when the page is not the native events archive (e.g. a page that lists events + news) where is_post_type_archive('events') is false.
     *
     * @param string   $where  Current WHERE clause
     * @param WP_Query $query  The query (WP 5.1+)
     * @return string Modified WHERE clause
     */
    public function stripPostDateForEventsQuery($where, $query)
    {
        if (is_admin() || !$query) {
            return $where;
        }
        $pt = $query->get('post_type');
        if (!$this->isEventPostType($pt)) {
            return $where;
        }
        $from = get_query_var('from', false) ?: (isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '');
        $to   = get_query_var('to', false) ?: (isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '');
        if (!$from && !$to) {
            return $where;
        }
        global $wpdb;
        $pattern = '/\s+AND\s*\(\s*' . preg_quote($wpdb->posts, '/') . '\.post_date\s*(>=|<=)\s*[\'"]?[0-9-]+[\'"]?\s*(?:\s+AND\s+' . preg_quote($wpdb->posts, '/') . '\.post_date\s*(>=|<=)\s*[\'"]?[0-9-]+[\'"]?)?\s*\)/i';
        return preg_replace($pattern, '', $where);
    }

    /**
     * Prevent Municipio's posts_where date filtering from interfering with events (when theme filter is used).
     * Strips out post_date conditions from WHERE clause when on events archive.
     *
     * @param string   $where Modified WHERE clause with date conditions
     * @param string|null $from From date
     * @param string|null $to To date
     * @return string WHERE clause without date conditions for events
     */
    public function skipEventDateFilter($where, $from, $to)
    {
        if (is_post_type_archive('events') || is_post_type_archive('event')) {
            global $wpdb;
            $pattern = '/\s+AND\s*\(\s*' . preg_quote($wpdb->posts, '/') . '\.post_date\s*(>=|<=)\s*[\'"]?[0-9-]+[\'"]?\s*(?:\s+AND\s+' . preg_quote($wpdb->posts, '/') . '\.post_date\s*(>=|<=)\s*[\'"]?[0-9-]+[\'"]?)?\s*\)/i';
            $where = preg_replace($pattern, '', $where);
            return $where;
        }
        return $where;
    }

    public function mergeTaxQuery($taxQuery, $query)
    {
        $postType = $query->get('post_type');
        $isEventArchive = is_post_type_archive('event') ||
                         is_post_type_archive('events') ||
                         ($postType && (is_array($postType) ? in_array('event', $postType) || in_array('events', $postType) : in_array($postType, ['event', 'events'])));

        if (!$isEventArchive) {
            return $taxQuery;
        }

        $slugs = isset($_GET['evenemangskategorier']) ? (array) $_GET['evenemangskategorier'] : [];
        $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $slugs))));

        if (!is_array($taxQuery)) {
            $taxQuery = [];
        }

        if ($slugs === []) {
            return $this->normalizeEvenemangskategorierTaxQueryOperators($taxQuery);
        }

        if (!isset($taxQuery['relation'])) {
            $taxQuery['relation'] = 'AND';
        }

        $merged = false;
        $taxQuery = $this->walkTaxQueryMergeEvenemangskategorier($taxQuery, $slugs, $merged);

        if (!$merged) {
            $taxQuery[] = [
                'taxonomy' => 'evenemangskategorier',
                'field'    => 'slug',
                'terms'    => $slugs,
                'operator' => 'IN',
            ];
        }

        return $taxQuery;
    }

    /**
     * Municipio uses operator AND for non-hierarchical taxonomies (match all terms). Multiselect needs IN (any).
     *
     * @param array $taxQuery Tax query array.
     * @return array
     */
    private function normalizeEvenemangskategorierTaxQueryOperators(array $taxQuery): array
    {
        $dummy = false;
        return $this->walkTaxQueryMergeEvenemangskategorier($taxQuery, [], $dummy);
    }

    /**
     * @param array $taxQuery Tax query.
     * @param array $slugs    Requested term slugs (empty = only fix operator on existing clauses).
     * @param bool  $merged   Set true when an evenemangskategorier clause was updated.
     * @return array
     */
    private function walkTaxQueryMergeEvenemangskategorier(array $taxQuery, array $slugs, &$merged): array
    {
        foreach ($taxQuery as $key => &$clause) {
            if ($key === 'relation') {
                continue;
            }
            if (!is_array($clause)) {
                continue;
            }
            if (isset($clause['taxonomy']) && $clause['taxonomy'] === 'evenemangskategorier') {
                $clause['operator'] = 'IN';
                $clause['field']    = 'slug';
                if ($slugs !== []) {
                    $clause['terms'] = $slugs;
                } elseif (isset($clause['terms']) && is_array($clause['terms'])) {
                    $clause['terms'] = array_values(array_unique(array_filter(array_map('sanitize_title', $clause['terms']))));
                }
                $merged = true;
                continue;
            }
            $clause = $this->walkTaxQueryMergeEvenemangskategorier($clause, $slugs, $merged);
        }
        unset($clause);

        return $taxQuery;
    }

    public function applyFilter($q)
    {
        // Check if we're on events archive - check both singular and plural
        $isEventArchive = !is_admin() && $q->is_main_query() && 
                          (is_post_type_archive('event') || is_post_type_archive('events'));
        
        if (!$isEventArchive) {
            return;
        }
        
        // Ensure post type is set correctly - use 'events' since that's what's in DB
        $currentPostType = $q->get('post_type');
        if (empty($currentPostType) || (!is_array($currentPostType) && !in_array($currentPostType, ['event', 'events']))) {
            if (is_post_type_archive('events')) {
                $q->set('post_type', 'events');
            } elseif (is_post_type_archive('event')) {
                $q->set('post_type', 'event');
            } else {
                $q->set('post_type', ['event', 'events']);
            }
        }

        // Taxonomy: Municipio/mergeTaxQuery already applied when PostFilters set tax_query. If it did not run
        // (no enabled filters), merge here once.
        $slugs = isset($_GET['evenemangskategorier']) ? (array) $_GET['evenemangskategorier'] : [];
        $slugs = array_values(array_unique(array_filter(array_map('sanitize_title', $slugs))));
        if ($slugs === []) {
            return;
        }
        $existing = $q->get('tax_query');
        if (!$this->taxQueryReferencesEvenemangskategorier(is_array($existing) ? $existing : null)) {
            $base = is_array($existing) ? $existing : [];
            if (!isset($base['relation'])) {
                $base['relation'] = 'AND';
            }
            $base[] = [
                'taxonomy' => 'evenemangskategorier',
                'field'    => 'slug',
                'terms'    => $slugs,
                'operator' => 'IN',
            ];
            $q->set('tax_query', $base);
        }
    }

    /**
     * @param array|null $taxQuery Tax query or null.
     */
    private function taxQueryReferencesEvenemangskategorier($taxQuery): bool
    {
        if (!is_array($taxQuery)) {
            return false;
        }
        foreach ($taxQuery as $key => $clause) {
            if ($key === 'relation') {
                continue;
            }
            if (!is_array($clause)) {
                continue;
            }
            if (isset($clause['taxonomy']) && $clause['taxonomy'] === 'evenemangskategorier') {
                return true;
            }
            if ($this->taxQueryReferencesEvenemangskategorier($clause)) {
                return true;
            }
        }
        return false;
    }

}

new Sater_Events_Filtering();
