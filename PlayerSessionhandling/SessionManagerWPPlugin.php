<?php
/*
Plugin Name: Session Manager
Plugin URI:  Your Plugin Info URL
Description: Manages user sessions to prevent multiple logins.
Version: 1.0
Author: Your Name
Author URI: Your Author URL
*/

// Hook to create table on plugin activation
register_activation_hook(__FILE__, 'create_session_table');

function create_session_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'active_sessions';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            session_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            session_token VARCHAR(255),
            login_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_active_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Add user session on login
add_action('wp_login', 'add_user_session', 10, 2);

function add_user_session($user_login, $user) {
    if (!is_user_logged_in()) {
        return; // Exit if the user is not logged in
    }

    global $wpdb;
    $user_id = $user->ID;
    $table_name = $wpdb->prefix . 'active_sessions';
    $session_token = bin2hex(random_bytes(16)); // Generate a secure random session token

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'session_token' => $session_token
        ),
        array('%d', '%s')
    );
}

// Remove user session on logout
add_action('wp_logout', 'remove_user_session');

function remove_user_session() {
    if (!is_user_logged_in()) {
        return; // Exit if the user is not logged in
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'active_sessions';
    $wpdb->delete($table_name, array('user_id' => $user_id), array('%d'));
}

// Scheduled cleanup of old sessions
if (!wp_next_scheduled('cleanup_old_sessions_hook')) {
    wp_schedule_event(time(), 'hourly', 'cleanup_old_sessions_hook');
}

add_action('cleanup_old_sessions_hook', 'cleanup_old_sessions');

function cleanup_old_sessions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'active_sessions';
    $wpdb->query("DELETE FROM $table_name WHERE last_active_timestamp < NOW() - INTERVAL 1 HOUR");
}
