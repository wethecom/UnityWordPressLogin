<?php
/*
Plugin Name: Online Hosts Manager
Description: Manages a list of online hosts from Unity Mirror Networking.
Version: 1.0
Author: Your Name
*/

function ohm_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "online_hosts";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        host_name text NOT NULL,
        players_current int DEFAULT 0,
        players_max int DEFAULT 100,
        status tinytext NOT NULL,
        tags text,
        banner_url text,
        server_address varchar(255),
        server_port smallint,
        last_online datetime DEFAULT CURRENT_TIMESTAMP,  // Timestamp of the last update
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function ohm_update_host_status( $request ) {
    global $wpdb;
    $data = $request->get_json_params();
    $table_name = $wpdb->prefix . "online_hosts";

    $update_data = [
        'status' => $data['status'],
        'players_current' => $data['players_current'],
        'players_max' => $data['players_max'],
        'tags' => implode(',', $data['tags']),
        'banner_url' => $data['banner_url'],
        'server_address' => $data['server_address'],
        'server_port' => $data['server_port'],
        'last_online' => current_time('mysql', 1)  // Use WordPress function to get the current time
    ];

    $exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM $table_name WHERE host_name = %s", $data['host_name']));
    if ($exists) {
        $wpdb->update($table_name, $update_data, ['host_name' => $data['host_name']]);
    } else {
        $wpdb->insert($table_name, array_merge(['host_name' => $data['host_name']], $update_data));
    }

    return new WP_REST_Response('Host status updated', 200);
}
