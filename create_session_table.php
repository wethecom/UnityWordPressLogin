<?php
require_once('wp-load.php'); // Ensure this path correctly points to wp-load.php

function create_session_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'active_sessions';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
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
        echo "Table created successfully.";
    } else {
        echo "Table already exists.";
    }
}
if (!wp_next_scheduled('cleanup_old_sessions_hook')) {
    wp_schedule_event(time(), 'hourly', 'cleanup_old_sessions_hook');
}
add_action('cleanup_old_sessions_hook', 'cleanup_old_sessions');
create_session_table();
?>
