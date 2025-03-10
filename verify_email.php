<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use Google\Client;
use Google\Service\Gmail;

$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Gmail::MAIL_GOOGLE_COM);
$client->setRedirectUri('https://admission.bcpsms3.com/verify_email.php');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

if (!isset($_SESSION['access_token']) && !isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} elseif (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $token;
    header('Location: ' . filter_var('https://admission.bcpsms3.com/verify_email.php', FILTER_SANITIZE_URL));
    exit;
} else {
    $client->setAccessToken($_SESSION['access_token']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $_SESSION['email'] = $email;

    // Generate a verification code
    $verification_code = rand(100000, 999999);
    $_SESSION['verification_code'] = $verification_code;

    // Refresh the token if it's expired
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        $_SESSION['access_token'] = $client->getAccessToken();
    }

    // Send verification email
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->AuthType = 'XOAUTH2';
        $mail->setOAuth(new OAuth([
            'provider' => new Google([
                'clientId' => $client->getClientId(),
                'clientSecret' => $client->getClientSecret(),
            ]),
            'clientId' => $client->getClientId(),
            'clientSecret' => $client->getClientSecret(),
            'refreshToken' => $client->getRefreshToken(),
            'userName' => 'noreply.bcpsms3@gmail.com',
        ]));
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('noreply.bcpsms3@gmail.com', 'Bestlink College');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body    = "Your verification code is: <b>$verification_code</b>";

        $mail->send();
        header('Location: verify_code.php');
        exit;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Bestlink College</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            overflow: hidden;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
        }

        .title {
            background-color: #1e3a8a;
            color: white;
            padding: 20px;
            margin-bottom: 2rem;
            text-align: center;
            border-radius: 15px 15px 0 0;
            position: relative;
            z-index: 1;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .proceed-btn {
            background-color: #1e3a8a;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .proceed-btn:hover {
            background-color: #15306d;
            transform: translateY(-2px);
        }

        body {
            overflow-x: hidden;
            position: relative;
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .title h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div style="position: absolute; top: 10px; left: 10px; z-index: 2;">
        <a href="login.php" style="text-decoration: none; background-color: #1e3a8a; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold;">
            &larr; Back
        </a>
    </div>
    <div>
        <div class="title text-center mb-10">
            <img src="assets/img/bcp.png" alt="School Logo" width="100">
            <h2 class="mt-3">Email Verification</h2>
            <p class="text">Please enter your email address to receive a verification code</p>
        </div>

        <div class="container mt-5">
            <div class="card">
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn proceed-btn">Send Verification Code</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>