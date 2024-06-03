<?php
// Include WordPress for authentication functions
require_once('wp-load.php');

// Check if the request is from Unity and has the required 'username' and 'password' POST fields
if(isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Attempt to authenticate the user
    $user = wp_authenticate($username, $password);

    if(is_wp_error($user)) {
        // Authentication failed
        echo json_encode(array('status' => 'error', 'message' => 'Invalid username or password'));
    } else {
        // Authentication successful
        echo json_encode(array('status' => 'success', 'message' => 'Login successful', 'userID' => $user->ID));
    }
} else {
    // Required fields are missing
    echo json_encode(array('status' => 'error', 'message' => 'Username and password are required'));
}
?>
