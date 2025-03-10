<?php
// Gmail API Configuration
define('GMAIL_API_CLIENT_ID', 'YOUR_CLIENT_ID');
define('GMAIL_API_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');
define('GMAIL_API_REDIRECT_URI', 'YOUR_REDIRECT_URI');

// OAuth 2.0 credentials
$client = new Google_Client();
$client->setClientId(GMAIL_API_CLIENT_ID);
$client->setClientSecret(GMAIL_API_CLIENT_SECRET);
$client->setRedirectUri(GMAIL_API_REDIRECT_URI);
$client->addScope('gmail.send');
?>