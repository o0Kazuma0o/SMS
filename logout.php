<?php
session_start();

// Clear all session data
session_unset();
session_destroy();

// Redirect to the login page
header("Location: admission.bcpsms3.com");
exit;