<?php
/*
Plugin Name: Display Online Hosts
Description: Displays a list of online hosts managed by the Online Hosts Manager plugin.
Version: 1.0
Author: Your Name
*/

// Function to display online hosts

ChatGPT
To ensure that the function display_online_hosts() checks if someone is logged in before displaying the online hosts, you can modify the function to include a check for user authentication. If the user is not logged in, you can either return a message or prevent the display of the host list.

Here's how you can add a login check to your script:

php
Copy code
function display_online_hosts() {
    if (!is_user_logged_in()) {
        return 'You must be logged in to see the online hosts.';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "online_hosts";
    $html = '<ul>';

    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'online'", ARRAY_A);
    if ($results) {
        foreach ($results as $host) {
            $html .= '<li>' . esc_html($host['host_name']) . '</li>';
        }
    } else {
        $html .= '<li>No hosts are currently online.</li>';
    }

    $html .= '</ul>';
    return $html;
}

add_shortcode('display_online_hosts', 'display_online_hosts');

?>
