<?php

namespace ModularityLatestNewsEvents\Module;

/**
 * Class LatestNews
 * @package LatestNews\Module
 */
class LatestNews extends \Modularity\Module
{
    public $slug = 'latest-news';
    public $supports = array();

    /**
     * Modularity wraps module output in a fragment cache (wp object cache; Redis here).
     * The parent default is 7 days (\Modularity\Module::$cacheTtl), so Blade changes
     * do not appear until the entry expires or Redis is flushed.
     */
    public $cacheTtl = 0;

    public function init()
    {
        $this->nameSingular = __('Senaste evenemang', 'modularity-latest-news');
        $this->namePlural = __('Senaste evenemang', 'modularity-latest-news');
        $this->description = __('Visar kommande evenemang. Antal och kolumner styrs i modulens inställningar.', 'modularity-latest-news');
    }

    /**
     * Data array
     * @return array $data
     */
    public function data(): array
    {
        $data = (array) \Modularity\Helper\FormatObject::camelCase($this->getFields());

        $data['objectType'] = $data['objectType'] ?? 'events';
        $data['gridColumnClass'] = $this->gridColumnClass((int) ($data['numberOfColumns'] ?? 4));
        $data['posts'] = [];

        if (($data['objectType'] ?? '') !== 'events') {
            return $data;
        }

        $data['posts'] = $this->getUpcomingEvents($data);

        return $data;
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return "latest-news.blade.php";
    }

    /**
     * Style - Register & adding css
     * @return void
     */
    public function style()
    {
        $path = MODULARITYLATEST_NEWS_PATH . 'assets/css/latest-events-module.css';
        if (!is_readable($path)) {
            return;
        }

        wp_register_style(
            'sater-latest-events-modularity',
            MODULARITYLATEST_NEWS_URL . 'assets/css/latest-events-module.css',
            [],
            (string) filemtime($path)
        );
        wp_enqueue_style('sater-latest-events-modularity');
    }

    /**
     * @param array<string, mixed> $data
     * @return \WP_Post[]
     */
    private function getUpcomingEvents(array $data): array
    {
        $numberOfItems = (int) ($data['numberOfItems'] ?? -1);
        if ($numberOfItems === 0) {
            return [];
        }

        $args = [
            'post_type'      => 'events',
            'posts_per_page' => $numberOfItems,
            'meta_key'       => 'start_datum',
            'orderby'        => [
                'meta_value' => 'ASC',
                'ID'         => 'ASC',
            ],
            'meta_query'     => $this->upcomingEventsMetaQuery(),
        ];

        if (!empty($data['filteringByCategorie']) && !empty($data['eventcategory'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'evenemangskategorier',
                    'field'    => 'term_id',
                    'terms'    => [(int) $data['eventcategory']],
                ],
            ];
        }

        return get_posts($args);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function upcomingEventsMetaQuery(): array
    {
        $nowYmdHi = function_exists('current_time') ? current_time('Y-m-d H:i') : date('Y-m-d H:i');
        $todayYmd = function_exists('current_time') ? current_time('Y-m-d') : date('Y-m-d');

        return [
            'relation' => 'OR',
            [
                'key'     => 'slut_datum',
                'value'   => $nowYmdHi,
                'compare' => '>=',
            ],
            [
                'key'     => 'slut_datum',
                'value'   => '',
                'compare' => '=',
            ],
            [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key'     => 'slut_datum',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => 'slut_datum',
                        'value'   => '',
                        'compare' => '=',
                    ],
                ],
                [
                    'key'     => 'start_datum',
                    'value'   => $todayYmd,
                    'compare' => '>=',
                ],
            ],
        ];
    }

    private function gridColumnClass(int $numberOfColumns): string
    {
        return match ($numberOfColumns) {
            1       => 'o-grid-12@md',
            2       => 'o-grid-6@md',
            3       => 'o-grid-4@md',
            default => 'o-grid-3@md',
        };
    }
}
