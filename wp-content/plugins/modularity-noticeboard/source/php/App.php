<?php

namespace ModularityNoticeboard;

class App {
    public $Roles = null;

    public function __construct() {
        $this->Roles = new \ModularityNoticeboard\Admin\Roles\General();

        //Register module
        add_action('plugins_loaded', array($this, 'registerModule'));

        // Create post type and taxonomy
        add_action('init', array($this, 'create_post_type'));
        add_action('init', array($this, 'create_taxonomy'));

        // Support for unpublished date
        add_filter('wp_insert_post_data', array($this, 'set_unpublish_date'), '99', 2);

        add_filter('wp_stream_record_array', array($this, 'modify_stream_title'));
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule() {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYNOTICEBOARD_PATH . 'source/php/Module/',
                'Noticeboard'
            );
        }
    }

    public function create_post_type() {
        register_post_type(
            MODULARITYNOTICEBOARD_POST_TYPE,
            array(
                'labels' => array(
                    'name' => __('Announcement'),
                    'singular_name' => __('Announcements')
                ),
                'public' => false,
                'show_ui' => true,
                'has_archive' => false,
                'rewrite' => false,
                'supports' => array('title'),
                'capability_type'     => array('mod_noticeboard_announcement', 'mod_noticeboard_announcements'),
                'map_meta_cap'        => true,
            )
        );
    }

    public function create_taxonomy() {
        $args = array(
            'hierarchical' => true,
            'label' => __('Announcement type'),
            'public' => false,
            'publicly_queryable' => false,
            'show_in_nav_menus' => false,
            'query_var' => false,
            'rewrite' => false,
        );

        register_taxonomy(MODULARITYNOTICEBOARD_TAXONOMY, MODULARITYNOTICEBOARD_POST_TYPE, $args);
    }
    /**
     * Set default unpublish date for new announcements. This sets POST data that later is consumed by
     * Content scheduler module.
     */
    public function set_unpublish_date($data, $postarr) {
        if ($data['post_type'] == MODULARITYNOTICEBOARD_POST_TYPE && isset($postarr['unpublish-active']) && $postarr['unpublish-active'] == 'false') {
            $unpublish_date = false;
            $city_council_term = get_term_by('slug', MODULARITYNOTICEBOARD_CITY_COUNCIL_SLUG, MODULARITYNOTICEBOARD_TAXONOMY);
            if ($city_council_term) {
                $city_council_term_id = $city_council_term->term_id;
            }
            $announcement_term = $postarr['acf'][MODULARITYNOTICEBOARD_ANNOUNCEMENT_TYPE_ACF_FIELD];;
            // Only set unpublish date if at least one term is selected.
            if (isset($city_council_term_id) && $city_council_term_id == $announcement_term) {
                // Unpublish the day after meeting day for city council announcments.
                $meeting_date = $postarr['acf'][MODULARITYNOTICEBOARD_MEETINGDATE_ACF_FIELD];
                if (!empty($meeting_date)) {
                    $unpublish_date = strtotime("$meeting_date + 1 day");
                }
            } else {
                // Unpublish 23 days after post date.
                $post_date = mysql2date('U', $postarr['post_date']);
                $unpublish_date = strtotime("+ 23 days", $post_date);
            }
            if ($unpublish_date) {
                $_POST['unpublish-action'] = 'draft';
                $_POST['unpublish-mm'] = date('m', $unpublish_date);
                $_POST['unpublish-jj'] = date('d', $unpublish_date);
                $_POST['unpublish-aa'] = date('Y', $unpublish_date);
                $_POST['unpublish-hh'] = '00';
                $_POST['unpublish-mn'] = '00';
            }
        }
        return $data;
    }

    /**
     * Add meeting date to stream log so it can be searched in free-text search.
     */
    public function modify_stream_title($record) {
        if ($record['context'] == MODULARITYNOTICEBOARD_POST_TYPE) {
            $meeting_date = isset($_POST['acf'][MODULARITYNOTICEBOARD_MEETINGDATE_ACF_FIELD]) ? $_POST['acf'][MODULARITYNOTICEBOARD_MEETINGDATE_ACF_FIELD] : false;
            if (!empty($meeting_date)) {
                $record['summary'] = $record['summary'] .  ' - ' . sprintf('Meeting date: %s', date('Y-m-d', strtotime($meeting_date)));
            }
            $announcement_type = isset($_POST['acf'][MODULARITYNOTICEBOARD_ANNOUNCEMENT_TYPE_ACF_FIELD]) ? $_POST['acf'][MODULARITYNOTICEBOARD_ANNOUNCEMENT_TYPE_ACF_FIELD] : false;
            if (!empty($announcement_type)) {
                $announcementType = get_term($announcement_type, MODULARITYNOTICEBOARD_TAXONOMY);
                $record['summary'] = $record['summary'] . sprintf(' - %s', $announcementType->name);
            }
        }

        return $record;
    }

    public function uninstall() {
        remove_role('noticeboard-admin');
    }
    public function install() {
        $this->Roles->addRole();
    }
}
