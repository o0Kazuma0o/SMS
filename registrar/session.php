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
        header("Location: admission.bcpsms3.com");
        exit;
    }

    // Check for session timeout
    //if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > INACTIVITY_TIMEOUT) {
    //    Session has expired due to inactivity
    //    session_unset();
    //    session_destroy();
    //    header("Location: /index.php?timeout=true");
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
            header("Location: admission.bcpsms3.com");
        }
        exit;
    }
}

function getCurrentActiveSemester($conn) {
    $stmt = $conn->prepare("SELECT name FROM sms3_semesters WHERE status = 'Active' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $semester = $result->fetch_assoc()['name'];
    $stmt->close();

    return $semester;
}