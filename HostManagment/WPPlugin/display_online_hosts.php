<?php
/*
Plugin Name: Display Online Hosts
Description: Displays a list of online hosts managed by the Online Hosts Manager plugin.
Version: 1.0
Author: Your Name
*/

// Function to display online hosts
function display_online_hosts() {
    global $wpdb;
    $table_name = $wpdb->prefix . "online_hosts";
    $html = '<ul>';

    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'online'", ARRAY_A);
    if($results) {
        foreach ($results as $host) {
            $html .= '<li>' . esc_html($host['host_name']) . '</li>';
        }
    } else {
        $html .= '<li>No hosts are currently online.</li>';
    }

    $html .= '</ul>';
    return $html;
}

// Register shortcode
add_shortcode('display_online_hosts', 'display_online_hosts');

?>
