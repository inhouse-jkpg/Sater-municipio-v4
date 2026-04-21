<?php

namespace Visit;

class PostTypes
{
    public function __construct()
    {
        add_action('init', [$this, 'setupPostTypes']);
        // hook into Municipios template loader and set our own single.blade.php
        add_filter('Municipio/blade/view_paths', [$this, 'addBladeViewPath']);
    }
    public function addBladeViewPath($paths)
    {
        $paths[] = plugin_dir_path(__DIR__) . '/views';
        return $paths;
    }
    public static function getPostTypes()
    {
        return [
        [
            'key'           => 'place',
            'hierarchical'  => false,
            'labels' => [
                'name'          => _x('Places', 'Post type pural', 'visit'),
                'singular_name' => _x('Place', 'Post type singular', 'visit'),
                'menu_name'     => _x('Places', 'Menu label', 'visit'),
            ],
            'menu_icon'     => 'dashicons-location',
            'rewrite'       =>  [
                'slug'                  => 'plats',
                'with_front'            => false,
                'pages'                 => true,
            ],
        ],
        [
            'key'           => 'guide',
            'hierarchical'  => false,
            'labels' => [
                'name'          => _x('Guides', 'Post type pural', 'visit'),
                'singular_name' => _x('Guide', 'Post type singular', 'visit'),
                'menu_name'     => _x('Guides', 'Menu label', 'visit'),
            ],
            'menu_icon' => 'dashicons-thumbs-up',
            'rewrite'       =>  [
                'slug'                  => 'guider',
                'with_front'            => false,
                'pages'                 => true,
            ],
        ]
        ];
    }

    public function setupPostTypes()
    {
        foreach (self::getPostTypes() as $postType) {
            self::registerPostType($postType);
        }
    }


   /**
    * Registers a post type.
    *
    * @param array postTypeArgs An array of arguments for the post type.
    *
    * @return WP_Post_Type|WP_Error The registered post type object on success, WP_Error object on ailure.
    */
    protected static function registerPostType(array $postTypeArgs = [])
    {
        // Post type key must exist
        if (empty($postTypeArgs['key'])) {
            return false;
        }

        // Default argument values.
        // Will be overwritten if existing in $postTypeArgs.
        $args = [
        'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'has_archive'           => false,
        'show_in_rest'          => true,
        'capability_type'       => 'page',
        'labels' => [
            'archives'              => __('Item Archives', 'visit'),
            'attributes'            => __('Item Attributes', 'visit'),
            'parent_item_colon'     => __('Parent Item:', 'visit'),
            'all_items'             => __('All Items', 'visit'),
            'add_new_item'          => __('Add New', 'visit'),
            'add_new'               => __('Add New', 'visit'),
            'new_item'              => __('New Item', 'visit'),
            'edit_item'             => __('Edit Item', 'visit'),
            'update_item'           => __('Update Item', 'visit'),
            'view_item'             => __('View Item', 'visit'),
            'view_items'            => __('View Items', 'visit'),
            'search_items'          => __('Search Item', 'visit'),
            'not_found'             => __('Not found', 'visit'),
            'not_found_in_trash'    => __('Not found in Trash', 'visit'),
            'featured_image'        => __('Featured Image', 'visit'),
            'set_featured_image'    => __('Set featured image', 'visit'),
            'remove_featured_image' => __('Remove featured image', 'visit'),
            'use_featured_image'    => __('Use as featured image', 'visit'),
            'insert_into_item'      => __('Insert into item', 'visit'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'visit'),
            'items_list'            => __('Items list', 'visit'),
            'items_list_navigation' => __('Items list navigation', 'visit'),
            'filter_items_list'     => __('Filter items list', 'visit'),
        ],
        ];

        foreach ($postTypeArgs as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $args[$key][$k] = $v;
                }
            } else {
                $args[$key] = $value;
            }
        }
        return register_post_type($postTypeArgs['key'], $args);
    }
}
