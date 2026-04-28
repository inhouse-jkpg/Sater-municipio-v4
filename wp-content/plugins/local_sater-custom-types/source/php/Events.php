<?php

namespace SaterCustomTypes;

class Events
{
    public function __construct()
    {
        add_action('init', array($this, 'registerPostType'));
				//$this->registerPostType(); // init redan körts

        add_action( 'rest_api_init', function () {
        	register_rest_route( 'wp/v2', '/latest-events/', array(
        		'methods' => 'GET',
        		'callback' => 'get_latest_events',
        	) );
        } );
    }

    public function registerPostType() {
      // Set UI labels for Custom Post Type
      	$labels = array(
      		'name'                => _x( 'Evenemang', 'Evenemang' ),
      		'singular_name'       => _x( 'Evenemang', 'Evenemang' ),
      		'menu_name'           => __( 'Evenemang' ),
      		'parent_item_colon'   => __( 'Föräldra evenemang' ),
      		'all_items'           => __( 'Alla evenemang' ),
      		'view_item'           => __( 'Granska evenemang' ),
      		'add_new_item'        => __( 'Lägg till nytt evenemang' ),
      		'add_new'             => __( 'Skapa ny' ),
      		'edit_item'           => __( 'Redigera evenemang' ),
      		'update_item'         => __( 'Uppdatera evenemang' ),
      		'search_items'        => __( 'Sök: Evenemang' ),
      		'not_found'           => __( 'Not Found' ),
      		'not_found_in_trash'  => __( 'Not found in Trash' ),
      	);

      // Set other options for Custom Post Type

      	$args = array(
      		'label'               => __( 'events' ),
      		'description'         => __( 'Evenemang'),
      		'labels'              => $labels,
      		// Features this CPT supports in Post Editor
      		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
      		// You can associate this CPT with a taxonomy or custom taxonomy.
      		'taxonomies'          => array( 'event-categories' ),
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
      		'rewrite'            => array( 'slug' => 'evenemang' ),
      		'can_export'          => true,
      		'has_archive'         => true,
      		'exclude_from_search' => false,
      		'publicly_queryable'  => true,
      		'capability_type'     => 'page',
      	);

      	// Registering your Custom Post Type
				register_post_type( 'events', $args );
    }

    /**
     * Setup a custom endpoint to get just the latest three news items
     */
    public function get_latest_events( $data ) {

    	$result_data = array();

    	$args = array(
    		'post_type'      => 'events',
    		'posts_per_page' => 100,
    	);
    	$events_query = new WP_Query( $args );
    	if ( $events_query->have_posts() ) : while ( $events_query->have_posts() ) : $events_query->the_post();
    		$result_data[] = array(
    			'link'  => get_post_permalink(),
    			'title' => get_the_title(),
    		);
    	endwhile; wp_reset_postdata(); endif;

    	return $result_data;
    }
}
