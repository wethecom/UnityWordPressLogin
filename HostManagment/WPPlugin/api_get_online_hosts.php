<?php
/*
Plugin Name: Get Online Hosts API
Description: Provides an API endpoint at /wp-json/api/v1/online-hosts/ to display online hosts as JSON and allows Unity to read the list. Requires user login for access.
Version: 1.0
Author: Your Name
*/

// Function to output online hosts as JSON
function api_get_online_hosts() {
    global $wpdb;
    $table_name = $wpdb->prefix . "online_hosts";
    $hosts = $wpdb->get_results("SELECT host_name FROM $table_name WHERE status = 'online'", ARRAY_A);

    return new WP_REST_Response($hosts, 200);
}

function register_online_hosts_routes() {
    register_rest_route('api/v1', '/online-hosts/', array(
        'methods' => 'GET',
        'callback' => 'api_get_online_hosts',
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
}
add_action('rest_api_init', 'register_online_hosts_routes');
?>
