<?php
/*
Plugin Name: Unity Auth
Description: Authenticate Unity game requests. REST API endpoint at /wp-json/unity/v1/authenticate/ that you can post the username and password fields to for authentication.
Version: 1.0
Author: Your Name
*/

add_action('rest_api_init', function () {
    register_rest_route('unity/v1', '/authenticate/', array(
        'methods' => 'POST',
        'callback' => 'unity_authenticate_user',
        'permission_callback' => '__return_true'
    ));
});

function unity_authenticate_user(WP_REST_Request $request) {
    $username = $request->get_param('username');
    $password = $request->get_param('password');

    if(empty($username) || empty($password)) {
        return new WP_Error('missing_credentials', 'Username and password are required', array('status' => 400));
    }

    $user = wp_authenticate($username, $password);

    if(is_wp_error($user)) {
        return new WP_Error('auth_failed', 'Invalid username or password', array('status' => 401));
    }

    return new WP_REST_Response(array('status' => 'success', 'message' => 'Login successful', 'userID' => $user->ID), 200);
}
?>