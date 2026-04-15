<?php
/**
 * AssetFlow v1.0 - Professional Logout Handler
 * This script securely clears user sessions and redirects with status flags.
 */

// 1. Initialize the session
session_start();

// 2. Unset all session variables (Clear data from memory)
$_SESSION = array();

// 3. If it's desired to kill the session, also delete the session cookie.
// This is a security best practice to ensure the cookie is physically removed from the browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server side
session_destroy();

// 5. Redirect back to login.php with a success parameter
// We use 'status=success' so we can trigger the Bootstrap alert on the login page.
header("Location: login.php?status=success");
exit();
?>