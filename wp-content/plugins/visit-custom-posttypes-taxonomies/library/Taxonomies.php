<?php

namespace Visit;

class Taxonomies
{
    public function __construct()
    {
        add_action('init', [$this, 'setupTaxonomies']);
        add_action('save_post_place', [$this,'normalizePlaceActivities'], 10, 3);
    }


    public static function getTaxonomies()
    {
        return
        [
        /**
         * TYP AV AKTIVITET (Sevärdhet, Äta & Dricka, Shopping osv)
         * (hierarchial)
         */
        [
            'labels'            => [
                'name'          => __('Activities', 'visit'),
                'singular_name' => __('Activity', 'visit'),
            ],
            'key'          => 'activity',
            'post_types'   => ['place', 'guide'],
            'hierarchical' => true,
            'rewrite' => array( 'hierarchical' => true ),
            'show_ui'      => true,
        ],        /**
        * VÄDERLEK (Solsken, Regn, Snö osv)
        * (non-hierarchial)
        */
        [
            'labels'            => [
                'name'          => __('Weathers', 'visit'),
                'singular_name' => _x('Weather', 'Singular term name', 'visit'),
            ],
            'key'          => 'weather',
            'post_types'   => ['place', 'guide'],
            'hierarchical' => false,
            'show_ui'      => true,
        ],
        /**
         * ÖVRIGT
         * (non-hierarchial)
         */
        [
            'labels'            => [
                'name'          => __('Other', 'visit'),
                'singular_name' => _x('Other', 'Singular term name', 'visit'),
            ],
            'key' => 'other',
            'post_types'        => 'place',
            'hierarchical'      => false,
            'show_ui'      => true,
        ],
        /**
         * TYP AV KÖK (Vegetariskt, Italienskt, Pizza, Husmanskost osv)
         * non-hierarchical
         */
        [
            'labels'            => [
                'name'          => _x('Cuisine', 'Singular term name', 'visit'),
                'singular_name' => _x('Cuisine', 'Singular term name', 'visit'),
            ],
            'key'               => 'cuisine',
            'post_types'        => 'place',
            'hierarchical'      => false,
            'show_ui'      => true,
            'rewrite' => [
                'slug' => 'typ-av-kok',
                'with_front' => false,
            ],
        ],
        ];
    }
    public function setupTaxonomies()
    {
        foreach (self::getTaxonomies() as $taxonomy) {
            self::registerTaxonomy($taxonomy);
        }
    }

    /**
     * Registers a taxonomy
     *
     * @param  array $taxonomyArgs An array of arguments for the taxonomy.
     *
     * @return WP_Taxonomy|WP_Error The registered taxonomy object on success, WP_Error object on failure.
     */
    protected static function registerTaxonomy(array $taxonomyArgs = [])
    {
        // Taxonomy key must exist
        if (empty($taxonomyArgs['key'])) {
            return false;
        }

        if (empty($taxonomyArgs['post_types'])) {
            $postTypes = [];
            if ($types = PostTypes::getPostTypes()) {
                foreach ($types as $type) {
                    $postTypes[] = $type['key'];
                }
            }
        } else {
            $postTypes = $taxonomyArgs['post_types'];
        }

        $args                    =  [
        'hierarchical'       => false,
        'show_ui'            => false,
        'public'             => true,
        'show_ui'            => false,
        'show_admin_column'  => true,
        'show_in_quick_edit' => true,
        'meta_box_cb'        => false,
        'show_in_nav_menus'  => false,
        'show_tagcloud'      => false,
        'rewrite'            => true,
        ];
        foreach ($taxonomyArgs as $key => $value) {
            if (is_array($value)) {
                $args[$key] = [];
                foreach ($value as $k => $v) {
                    $args[$key][$k] = $v;
                }
            } else {
                $args[$key] = $value;
            }
        }
        return register_taxonomy($taxonomyArgs['key'], $postTypes, $args);
    }
    /**
     * It checks if the post has any activities selected, and if so, it checks if any of the
     * activities' parents are not selected, and if so, it adds them to the list of selected activities
     *
     * @param postId The ID of the post being saved
     * @param post The post object.
     * @param update true if this is an existing post being updated, false if it's a new post
     */
    public function normalizePlaceActivities($postId, $post, $update)
    {
        if (isset($_POST['acf']['field_63dcbd00231bd'])) {
            $termIds = $_POST['acf']['field_63dcbd00231bd'];
            foreach ($termIds as $termId) {
                $term = get_term_by('term_id', $termId, 'activity');
                if (!is_wp_error($term)) {
                    $ancestors = get_ancestors($term->term_id, $term->taxonomy);
                    if (!is_wp_error($ancestors)) {
                        foreach ($ancestors as $ancestorId) {
                            if (!in_array($ancestorId, $_POST['acf']['field_63dcbd00231bd'], true)) {
                                array_push($_POST['acf']['field_63dcbd00231bd'], $ancestorId);
                            }
                        }
                    }
                }
            }
        }
    }
}
