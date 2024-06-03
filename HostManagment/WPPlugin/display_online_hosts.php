<?php
/*
Plugin Name: Display Online Hosts
Description: Displays a list of online hosts managed by the Online Hosts Manager plugin  shortcode [display_online_hosts].
Version: 1.0
Author: Your Name
*/
function display_online_hosts() {
    if (!is_user_logged_in()) {
        return 'You must be logged in to see the online hosts.';
    }

    // Handle search
    $search_query = isset($_GET['search']) ? $_GET['search'] : '';

    global $wpdb;
    $table_name = $wpdb->prefix . "online_hosts";
    $html = '<div>';

    // Search form
    $html .= '<form action="" method="get">
                <input type="text" name="search" placeholder="Search hosts..." value="' . esc_attr($search_query) . '">
                <button type="submit">Search</button>
              </form>';

    // Query to fetch data
    $query = "SELECT * FROM $table_name WHERE status = 'online'";
    if (!empty($search_query)) {
        $query .= $wpdb->prepare(" AND host_name LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
    }
    $results = $wpdb->get_results($query, ARRAY_A);

    // Display results
    if ($results) {
        $html .= '<ul>';
        foreach ($results as $host) {
            $html .= '<li>' . esc_html($host['host_name']) . ' - ' . 
                     esc_html($host['players_current']) . '/' . esc_html($host['players_max']) . ' Players - ' .
                     esc_html($host['tags']) . ' - Last online: ' . esc_html($host['last_online']) .
                     '<br><img src="' . esc_url($host['banner_url']) . '" style="width:200px;"><br>' .
                     'Address: ' . esc_html($host['server_address']) . ':' . esc_html($host['server_port']) . '</li>';
        }
        $html .= '</ul>';
    } else {
        $html .= 'No hosts match your search.';
    }

    $html .= '</div>';
    return $html;
}

add_shortcode('display_online_hosts', 'display_online_hosts');

?>
