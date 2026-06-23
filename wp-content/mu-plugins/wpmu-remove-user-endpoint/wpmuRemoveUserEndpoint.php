<?php
/*
Plugin Name:    WPMU Remove user endpoint
Description:    Removes user endpoint if not logged in.
Version:        1.0.0
Author:         Niclas Norin
*/

function removeUserEndpointIfNotLoggedIn() {
    if (!is_user_logged_in()) {
        add_filter('rest_prepare_user', function ($response, $user, $request) {
            return new WP_Error('rest_forbidden', 'Forbidden: Access to the requested resource is forbidden. You may not have the necessary permissions to access this resource. Please contact the administrator if you believe this is an error.', array('status' => 403));
        }, 10, 3);
    }
}

add_action('init', 'removeUserEndpointIfNotLoggedIn');
