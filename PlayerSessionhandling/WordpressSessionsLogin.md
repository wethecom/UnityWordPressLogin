To integrate session management for user login with a WordPress-based system, you'll need to adjust the process slightly to work within the WordPress environment. This approach involves leveraging WordPress's user management system while adding custom session tracking functionalities. Here's how you can integrate it with a custom independent page in WordPress, not as a plugin:

### Step 1: WordPress Database Access

WordPress uses a global `$wpdb` object for database operations. You can use this object to interact with your database instead of using raw MySQLi or PDO.

### Step 2: Create a Session Tracking Table

You should still create a table to manage sessions, similar to the previous example. You can use the WordPress database for this:

```sql
CREATE TABLE wp_active_sessions (
    session_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(255),
    login_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_active_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES wp_users(ID)
);
```

### Step 3: Generate Session Token and Check Existing Sessions

When a user logs in, generate a unique session token and check for existing sessions using WordPress's database methods.

```php
global $wpdb;
$user_id = get_current_user_id(); // Get WordPress logged in user ID

// Check if there is an existing session
$session_exists = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM wp_active_sessions WHERE user_id = %d",
    $user_id
));

if ($session_exists > 0) {
    // User already has an active session
    echo "You are already logged in elsewhere.";
    exit;
} else {
    // Proceed with new session
    $session_token = bin2hex(random_bytes(16));
    $wpdb->insert(
        'wp_active_sessions',
        array(
            'user_id' => $user_id,
            'session_token' => $session_token
        ),
        array('%d', '%s')
    );
}
```

### Step 4: Handle Logout

Modify the WordPress logout process to remove the session from the `wp_active_sessions` table.

```php
add_action('wp_logout', 'remove_user_session');

function remove_user_session() {
    global $wpdb;
    $user_id = get_current_user_id(); // Get the logging out user's ID
    $wpdb->delete(
        'wp_active_sessions',
        array('user_id' => $user_id),
        array('%d')
    );
}
```

### Step 5: Session Expiry

You could implement a cron job in WordPress to handle session expiry. This can be set up to run at regular intervals, deleting old sessions:

```php
add_action('wp', 'schedule_session_cleanup');

function schedule_session_cleanup() {
    if (!wp_next_scheduled('session_cleanup_event')) {
        wp_schedule_event(time(), 'hourly', 'session_cleanup_event');
    }
}

add_action('session_cleanup_event', 'cleanup_old_sessions');

function cleanup_old_sessions() {
    global $wpdb;
    $wpdb->query("DELETE FROM wp_active_sessions WHERE last_active_timestamp < NOW() - INTERVAL 1 HOUR");
}
```

### Integration with Custom Independent Page

If you're creating an independent page outside the standard WordPress template hierarchy, you can still use WordPress functions by including `wp-load.php` to bootstrap WordPress and gain access to its functions and the database:

```php
require_once('path_to_wp/wp-load.php');

// Now you can use WordPress functions and global objects
```

This setup allows you to handle user sessions on custom pages within a WordPress context, ensuring that you can manage logins and sessions effectively without creating a plugin.



To create an installation script for setting up the `wp_active_sessions` table within the WordPress environment, you can create a PHP script that checks if the table exists and, if not, creates it. This script can be run as part of a custom page or a plugin activation routine.

Here’s a step-by-step script for this purpose:

### PHP Installation Script

```php
<?php
require_once('wp-load.php'); // Path to wp-load.php to initialize WordPress environment

function create_session_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'active_sessions';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        // Table does not exist, so create it
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

// Call the function to check and create the table
create_session_table();
?>
```

### Explanation

1. **WordPress Initialization**: The script starts by including `wp-load.php` to bootstrap WordPress, which provides access to WordPress functions and the global `$wpdb` object.

2. **Function Definition**: `create_session_table()` function checks if the table already exists in the WordPress database using a `SHOW TABLES LIKE` SQL statement. It ensures you don't attempt to recreate the table if it's already there.

3. **Table Creation**: If the table does not exist, the script constructs a SQL query to create it. The `$wpdb->prefix` is used to prepend the table prefix defined in your WordPress configuration (usually `wp_`), making sure it integrates neatly with the existing WordPress database structure.

4. **Executing SQL**: The `dbDelta()` function from WordPress's upgrade script handles the SQL execution. This function is robust and manages SQL table creation and updates efficiently.

5. **Running the Script**: This script is intended to be run once manually, or it could be part of a larger plugin activation hook or theme setup routine.

### Usage

- **Manual Execution**: Save this script as `create_table.php` in your WordPress directory and access it directly via your browser to run it once.
- **Integration**: Alternatively, incorporate this script into a plugin's activation hook or a theme's setup function to automate the process when the theme or plugin is activated.

This approach ensures that your custom session management table integrates well with any WordPress installation and can be managed within the WordPress ecosystem.
