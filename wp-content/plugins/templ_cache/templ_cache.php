<?php
/**
 * Plugin Name: Templ Cache
 * Description: Helper plugin for the Templ server cache. Purges the cache automatically when content changes. Also includes a manual purge button.
 * Version: 1.4.2
 * Update URI: false
 * Author: Templ
 * Author URI: https://templ.io/
 * Text Domain: templio-cache
 * Domain Path: /languages
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Useful constants:
 * - TEMPL_DEBUG (set to true to enable debugging features)
 * 
 * Useful filters:
 * - templio_cache_purge_actions (array of action hooks that when fired should purge the cache)
 * - templio_cache_excluded_post_types (array of post types that we don't want to purge the cache when edited)
 * - templio_cache_excluded_post_statuses (array of post statuses that we don't want to purge the cache when edited)
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('TEMPLIO_CACHE_DIR_PATH', plugin_dir_path(__FILE__));
define('TEMPLIO_CACHE_DIR_URL', plugin_dir_url(__FILE__));
define('TEMPLIO_CACHE_BASENAME', plugin_basename(__FILE__));

class templioCache {

    private $screen = 'tools_page_templio-cache';
    private $capability = 'manage_options';
    private $admin_page = 'tools.php?page=templio-cache';

    public function __construct() {

        add_action( 'init', array( $this, 'wpdocs_load_textdomain') );

        add_filter( 'templio_cache_purge_actions', [$this, 'default_purge_actions'], 0, 1) ;
        add_filter( 'option_templio_auto_purge', 'absint' );
        add_filter( 'plugin_action_links_' . TEMPLIO_CACHE_BASENAME, array( $this, 'add_plugin_actions_links' ) );

        if ( get_option( 'templio_auto_purge' ) ) {
            add_action( 'init', array( $this, 'register_purge_actions' ), 20 );
            add_action( 'transition_post_status', array( $this, 'post_status_transition' ), 10, 3 );
        }

        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu_page' ) );
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_node' ), 100 );
        add_action( 'load-' . $this->screen, array( $this, 'do_admin_actions' ) );
        add_action( 'load-' . $this->screen, array( $this, 'add_settings_notices' ) );

        add_action( 'templ_after_cache_settings', array( $this, 'print_debug' ) );

        register_activation_hook( __FILE__, array($this,'set_default_settings') );
        register_activation_hook( __FILE__, array($this,'activate_cache') );
        register_deactivation_hook( __FILE__, array($this,'deactivate_cache') );

        add_filter('site_status_page_cache_supported_cache_headers', array($this, 'make_wp_health_check_detect_templ_cache'));

    }

    public function wpdocs_load_textdomain() {
        load_plugin_textdomain( 'templio-cache', false, dirname( TEMPLIO_CACHE_BASENAME ) . '/languages' ); 
    }

    public function register_purge_actions() {

        // use `templio_cache_purge_actions` filter to alter default purge actions
        $purge_actions = (array) apply_filters('templio_cache_purge_actions', []);

        foreach ( $purge_actions as $action ) {
            add_action( $action, array( $this, 'purge_zone_once' ), 9999, 2 );
        }

    }

    public function default_purge_actions($actions) {

        $default_actions = [
            'publish_phone',
            'save_post',
            'edit_post',
            'delete_post',
            'wp_trash_post',
            'clean_post_cache',
            'trackback_post',
            'pingback_post',
            'comment_post',
            'edit_comment',
            'delete_comment',
            'wp_set_comment_status',
            'switch_theme',
            'wp_update_nav_menu',
            'autoptimize_action_cachepurged', // Autoptimize's is purged
            'update_option_sidebars_widgets', // When you change the order of widgets.
            'update_option_category_base', // When category permalink is updated.
            'update_option_tag_base', // When tag permalink is updated.
            'permalink_structure_changed', // When tag permalink is updated.
            'add_link', // When a link is added.
            'edit_link', // When a link is updated.
            'delete_link', // When a link is deleted.
            'customize_save_after', // When customizer is saved.
            'update_option_theme_mods_' . get_option( 'stylesheet' ), // When theme customizations is saved
            'after_rocket_clean_file', // When WP Rocket cache of a single page is cleared
            'after_rocket_clean_domain', // When WP Rocket cache of a domain is cleared
            'after_rocket_clean_cache_dir', // When WP Rocket cache is cleared
            'after_rocket_clean_minify', // When WP Rocket's minify cache files is deleted.
            'fusion_options_save', // When saving Avada theme options
            'elementor/core/files/clear_cache', // Elementor cache is cleared
            'et_save_post', // Divi saves a post(?)
            'et_epanel_changing_options', // Divi options changed(?)
            'et_core_static_resources_removed', // Divi's static resouces are removed
            'nitropack_integration_purge_all', // Nitropack
            'nitropack_integration_purge_url', // Nitropack
            'ava_generate_styles', // Enfold theme generates styles
        ];

        return array_merge($actions, $default_actions);

    }

    public function register_settings() {

        register_setting( 'templio-cache', 'templio_auto_purge' );

    }

    public function add_settings_notices() {

        if ( isset( $_GET[ 'message' ] ) && ! isset( $_GET[ 'settings-updated' ] ) ) {

            // show cache purge success message
            if ( $_GET[ 'message' ] === 'cache-purged' ) {
                add_settings_error( '', 'templio_cache_path', __( 'Cache purged.', 'templio-cache' ), 'updated' );
            }

            // show cache purge failure message
            if ( $_GET[ 'message' ] === 'purge-cache-failed' ) {
                add_settings_error( '', 'templio_cache_path', __( 'Cache could not be purged. ', 'templio-cache' ) );
            }

        }
    }

    public function do_admin_actions() {

        // purge cache
        if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'purge-cache' && wp_verify_nonce( $_GET[ '_wpnonce' ], 'purge-cache' ) ) {

            $result = $this->purge_zone( false, false );
            wp_safe_redirect( admin_url( add_query_arg( 'message', is_wp_error( $result ) ? 'purge-cache-failed' : 'cache-purged', $this->admin_page ) ) );
            exit;

        }

    }

    public function add_admin_bar_node( $wp_admin_bar ) {

        // verify user capability
        if ( ! current_user_can( $this->capability ) ) {
            return;
        }

        // add "Templ" node to admin-bar
        $wp_admin_bar->add_node( array(
            'id' => 'templ',
            'title' => __( 'Templ', 'templio-cache' ),
            'href' => admin_url( $this->admin_page )
        ) );

        // add "Purge Cache" to "Templ" node
        $wp_admin_bar->add_node( array(
            'parent' => 'templ',
            'id' => 'templ-purge-cache',
            'title' => __( 'Purge cache', 'templio-cache' ),
            'href' => wp_nonce_url( admin_url( add_query_arg( 'action', 'purge-cache', $this->admin_page ) ), 'purge-cache' )
        ) );

        // add "Settings" to "Templ" node
        $wp_admin_bar->add_node( array(
            'parent' => 'templ',
            'id' => 'templ-cache-settings',
            'title' => __( 'Settings', 'templio-cache' ),
            'href' => admin_url( $this->admin_page )
        ) );

    }

    public function add_admin_menu_page() {

        // add "Tools" sub-page
        add_management_page(
            __( 'Templ Cache', 'templio-cache' ),
            __( 'Templ Cache', 'templio-cache' ),
            $this->capability,
            'templio-cache',
            array( $this, 'show_settings_page' )
        );

    }

    public function show_settings_page() {
        require_once TEMPLIO_CACHE_DIR_PATH . '/includes/settings-page.php';
    }

    public function add_plugin_actions_links( $links ) {

        // add settings link to plugin actions
        return array_merge(
            array( '<a href="' . admin_url( $this->admin_page ) . '">' . __('Settings', 'templio-cache') . '</a>' ),
            $links
        );

    }

    public function get_app_id() {

        if( isset( $_SERVER['TEMPL_APP_ID'] ) ) {
            return $_SERVER['TEMPL_APP_ID'];
        }

        if( get_option('templio_app_id') ) {
            return get_option('templio_app_id');
        }

        return false;

    }

    public function purge_zone_once( $id = false, $arg2 = false ) {

        static $purge_completed = false;

        if ( ! $purge_completed ) {

            $purged = $this->purge_zone( $id, $arg2 );

            if($purged) {
                $purge_completed = true;
            }

        }

    }

    public function get_local_ip() {
        return getHostByName(php_uname('n'));
    }

    private function get_action_url( string $action ) {
        
        $url = 'http://'.$this->get_local_ip().'/.well-known/'.$action.'/?id='.$this->get_app_id();

        if( $action === 'purge-cache' || $action === 'deactivate-cache' || $action === 'activate-cache' ) {
            return $url;
        }

        return false;

    }

    private function purge_zone( $id, $arg2 ) {

        if ( ! $this->should_purge( $id, $arg2 ) ) {
            return false;
        }

        $res = wp_remote_get( $this->get_action_url('purge-cache'), ['blocking' => false] );

        if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
            error_log( 'zone purged' );
        }

        do_action('templ_cache_purged');
        
        return true;

    }

    public function deactivate_cache() {

        $res = wp_remote_get( $this->get_action_url('deactivate-cache'), ['blocking' => false] );

        return true;

    }

    public function activate_cache() {

        $res = wp_remote_get( $this->get_action_url('activate-cache'), ['blocking' => false] );

        return true;

    }

    private function should_purge( $id, $arg2 ) {

        if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
            error_log( '$id ' . $id . ' made it to should_purge()' );
        }

        if( ! $id ) {
            return true;
        }

        // If new comment
        if( $arg2 === 0 || $arg2 === 'spam' ) {

            if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
                error_log( 'cache not perged because post was non-approved comment' );
            }

            return false;

        }

        if( ! is_object( $arg2 ) ) {
            return true;
        }

        // Asume $arg2 is a WP Post object
        $post = $arg2;

        if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
            error_log( 'should_purge $post->post_type: ' . $post->post_type . ' (' . $post->post_status . ')');
        }

        // Exclude certain post types from purging cache
        if ( in_array( $post->post_type, (array) apply_filters( 'templio_cache_excluded_post_types', array( 'revision', 'shop_order', 'nav_menu_item', 'attachment', 'shop_coupon' ) ) ) ) {

            if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
                error_log( 'cache not perged because $post->post_type: ' . $post->post_type );
            }

            return false;

        }

        // Exclude certain post statuses from purging cache
        if ( in_array( $post->post_status, (array) apply_filters( 'templio_cache_excluded_post_statuses', array( 'auto-draft', 'draft' ) ) ) ) {

            if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
                error_log( 'cache not perged because $post->post_status: ' . $post->post_status );
            }

            return false;

        }

        return true;

    }

    public function print_debug() {
        if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
            $debug_info = [
                '$_SERVER[\'TEMPL_APP_ID\']' => isset($_SERVER['TEMPL_APP_ID']) ? $_SERVER['TEMPL_APP_ID'] : null,
                'templio_app_id' => get_option('templio_app_id'),
                'get_app_id()' => $this->get_app_id(),
                'deactivate-cache' => $this->get_action_url('deactivate-cache'),
                'activate-cache' => $this->get_action_url('activate-cache'),
                'purge-cache' => $this->get_action_url('purge-cache'),
                'templio_auto_purge' => (bool) get_option('templio_auto_purge'),
                'purge_actions' => apply_filters('templio_cache_purge_actions', []),
            ];
            echo '<pre>';
            var_dump($debug_info);
            echo '</pre>';
        }
    }

    public function make_wp_health_check_detect_templ_cache($cache_headers) {
        if( is_array($cache_headers) && ! isset($cache_headers['x-cache-status']) ) {
            $cache_headers['x-cache-status'] = static function ( $header_value ) {
                return false !== strpos( strtolower( $header_value ), 'hit' );
            };
        }
        return $cache_headers;
    }

    function set_default_settings() {
        if( get_option('templio_auto_purge') !== false ) {
            return;
        }
        update_option('templio_auto_purge', 1, true);
    }

    // Make sure that cache is purged when an auto-draft is published
    function post_status_transition( string $new_status, string $old_status, WP_Post $post ) {
        if( $old_status == 'auto-draft' && $new_status == 'publish' ) {
            if( defined('TEMPL_DEBUG') && TEMPL_DEBUG ) {
                error_log( 'Purging cache because auto-draft -> publish.' );
            }
            $this->purge_zone_once($post->ID, $post);
        }
    }

}

new templioCache;

if( defined('WP_CLI') && WP_CLI ) {
    require_once(TEMPLIO_CACHE_DIR_PATH.'/includes/cli.php');
}