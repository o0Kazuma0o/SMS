<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
require_once '../database.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Set the inactivity timeout (in seconds)
define('INACTIVITY_TIMEOUT', 300); // 300 seconds = 5 minutes

// Function to check session expiration and access control
function checkAccess($requiredRole) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: /SMS/index.php");
        exit;
    }

    // Check for session timeout
    //if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > INACTIVITY_TIMEOUT) {
    //    Session has expired due to inactivity
    //    session_unset();
    //    session_destroy();
    //    header("Location: /SMS/index.php?timeout=true");
    //    exit;
    //}

    // Update last activity time
    //$_SESSION['last_activity'] = time();

    // Check if the user's role matches the required role
    if ($_SESSION['role'] !== $requiredRole) {
        // Redirect to the previous page if they are not authorized
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: /SMS/index.php");
        }
        exit;
    }
}

/**
 * Function to log in a user and set session variables
 */
function loginUser($username, $password) {
    global $conn;

    // Fetch the user from the database
    $stmt = $conn->prepare("SELECT id, username, password, name, role, email, phone FROM sms3_user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password and log the user in
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];

        exit; // Ensure script stops after redirect
    } else {
        return false; // Invalid login
    }
}
