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
    return "(SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'start_datum' LIMIT 1) ASC, {$wpdb->posts}.ID ASC";
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
    // Keep events visible until their end date has passed.
    // If an event is missing slut_datum, fall back to start_datum.
    $now  = function_exists('current_time') ? current_time('Y-m-d H:i') : date('Y-m-d H:i');
    $date = function_exists('current_time') ? current_time('Y-m-d') : date('Y-m-d');
    $where .= $wpdb->prepare(
        " AND (
            {$wpdb->posts}.ID IN (
                SELECT post_id FROM {$wpdb->postmeta}
                WHERE meta_key = 'slut_datum' AND meta_value >= %s
            )
            OR (
                {$wpdb->posts}.ID NOT IN (
                    SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'slut_datum'
                )
                AND {$wpdb->posts}.ID IN (
                    SELECT post_id FROM {$wpdb->postmeta}
                    WHERE meta_key = 'start_datum' AND meta_value >= %s
                )
            )
        )",
        $now,
        $date
    );
    return $where;
}, 10, 2);
