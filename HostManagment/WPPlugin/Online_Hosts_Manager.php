<?php
/*
Plugin Name: Online Hosts Manager
Description: Manages a list of online hosts from Unity Mirror Networking.
Version: 1.0
Author: Your Name
*/

// Activation hook for creating database table
function ohm_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "online_hosts";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        host_name text NOT NULL,
        status tinytext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'ohm_create_table' );

// REST API endpoint to handle data from Unity
function ohm_register_routes() {
    register_rest_route( 'ohm/v1', '/update/', array(
        'methods' => 'POST',
        'callback' => 'ohm_update_host_status',
    ));
}
add_action( 'rest_api_init', 'ohm_register_routes' );

// Callback to update or add host status
function ohm_update_host_status( $request ) {
    global $wpdb;
    $data = $request->get_json_params();
    $table_name = $wpdb->prefix . "online_hosts";

    // Check if host exists and update or insert accordingly
    $exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM $table_name WHERE host_name = %s", $data['host_name']));
    if ($exists) {
        $wpdb->update($table_name, ['status' => $data['status']], ['host_name' => $data['host_name']]);
    } else {
        $wpdb->insert($table_name, [
            'host_name' => $data['host_name'],
            'status' => $data['status']
        ]);
    }

    return new WP_REST_Response('Host status updated', 200);
}

?>
