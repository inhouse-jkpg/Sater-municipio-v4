<?php

namespace SaterCustomTypes;

class News
{
    public function __construct()
    {
        add_action('init', array($this, 'registerPostType'));
		//$this->registerPostType(); # init redan körts
        add_action( 'rest_api_init', function () {
            register_rest_route( 'wp/v2', '/latest-news/', array(
                'methods' => 'GET',
                'callback' => 'get_latest_news',
            ) );
        } );
    }

    public function registerPostType() {

        // Set UI labels for Custom Post Type
    	$labels = array(
    		'name'                => _x( 'Nyheter', 'Nyheter' ),
    		'singular_name'       => _x( 'Nyhet', 'Nyhet' ),
    		'menu_name'           => __( 'Nyheter' ),
    		'parent_item_colon'   => __( 'Föräldra nyhet' ),
    		'all_items'           => __( 'Alla nyheter' ),
    		'view_item'           => __( 'Granska nyhet' ),
    		'add_new_item'        => __( 'Lägg till nyhet' ),
    		'add_new'             => __( 'Skapa ny' ),
    		'edit_item'           => __( 'Redigera nyhet' ),
    		'update_item'         => __( 'Uppdatera nyhet' ),
    		'search_items'        => __( 'Sök: Nyhet' ),
    		'not_found'           => __( 'Not Found' ),
    		'not_found_in_trash'  => __( 'Not found in Trash' ),
    	);

        // Set other options for Custom Post Type
    	$args = array(
    		'label'               => __( 'Nyheter' ),
    		'description'         => __( 'Nyheter'),
    		'labels'              => $labels,
    		// Features this CPT supports in Post Editor
    		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
    		// You can associate this CPT with a taxonomy or custom taxonomy.
    		'taxonomies'          => array( 'news-categories' ),
    		/* A hierarchical CPT is like Pages and can have
    		* Parent and child items. A non-hierarchical CPT
    		* is like Posts.
    		*/
    		'hierarchical'        => false,
    		'public'              => true,
    		'show_ui'             => true,
    		'show_in_menu'        => true,
    		'show_in_nav_menus'   => true,
    		'show_in_admin_bar'   => true,
    		'menu_position'       => 5,
    		'can_export'          => true,
    		'has_archive'         => true,
    		'exclude_from_search' => false,
    		'publicly_queryable'  => true,
    		'rewrite'            => array( 'slug' => 'nyheter' ),
    		'capability_type'     => 'page',
    	);

    	// Registering your Custom Post Type
    	register_post_type( 'news', $args );
    }

    /**
     * Setup a custom endpoint to get just the latest three news items
     */
    public function get_latest_news( $data ) {

    	$result_data = array();

    	$args = array(
    		'post_type'      => 'news',
    		'posts_per_page' => 100,
    	);
    	$news_query = new WP_Query( $args );
    	if ( $news_query->have_posts() ) : while ( $news_query->have_posts() ) : $news_query->the_post();
    		$result_data[] = array(
    			'link'  => get_post_permalink(),
    			'title' => get_the_title(),
    		);
    	endwhile; wp_reset_postdata(); endif;

    	return $result_data;
    }
}
