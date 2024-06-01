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
