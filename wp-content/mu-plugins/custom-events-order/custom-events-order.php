<?php
/**
 * Plugin Name: Custom Events Order
 * Description: Forces the 'events' post type to sort by start_datum ASC and hides past events on the frontend.
 */

add_filter('posts_orderby', function ($orderby, $query) {
    if (!in_array('events', (array) $query->get('post_type'))) {
        return $orderby;
    }
    global $wpdb;
    return "(SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'start_datum' LIMIT 1) ASC";
}, 10, 2);

add_filter('posts_where', function ($where, $query) {
    if (is_admin()) {
        return $where;
    }
    if (!in_array('events', (array) $query->get('post_type'))) {
        return $where;
    }
    if (!empty($_GET['date_from']) || !empty($_GET['dateFrom'])) {
        return $where;
    }
    global $wpdb;
    $today = date('Y-m-d');
    $where .= $wpdb->prepare(
        " AND {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'start_datum' AND meta_value >= %s)",
        $today
    );
    return $where;
}, 10, 2);
