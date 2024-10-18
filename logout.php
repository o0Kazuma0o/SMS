<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Redirect to the login page
header("Location: /SMS/index.php");
exit;