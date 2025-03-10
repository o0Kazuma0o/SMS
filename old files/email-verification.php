<?php
session_start();
require('database.php');
require('oauth2-credentials.php');

// Check if user is already logged in and email is verified
if (isset($_SESSION['verified']) && $_SESSION['verified']) {
    header('Location: select_branch.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Check if email exists in database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate verification code
        $verification_code = rand(100000, 999999);
        
        // Update user with verification code
        $update_query = "UPDATE users SET verification_code = '$verification_code' WHERE email = '$email'";
        mysqli_query($conn, $update_query);
        
        // Send verification email using Gmail API
        try {
            $mailer = new PHPMailer();
            $mailer->isSMTP();
            $mailer->Host = 'smtp.gmail.com';
            $mailer->Port = 587;
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->SMTPAuth = true;
            
            // Use your Gmail API credentials
            $mailer->Username = 'YOUR_GMAIL_ADDRESS';
            $mailer->Password = 'YOUR_GMAIL_APP_PASSWORD';
            
            $mailer->setFrom('YOUR_GMAIL_ADDRESS', 'Bestlink College');
            $mailer->addAddress($email);
            
            $mailer->isHTML(true);
            $mailer->Subject = 'Email Verification - Bestlink College';
            $mailer->Body = "
                <h2>Email Verification</h2>
                <p>Your verification code is: $verification_code</p>
                <p>Please enter this code on the verification page.</p>
            ";
            
            if ($mailer->send()) {
                $_SESSION['email'] = $email;
                header('Location: verify-code.php');
                exit;
            } else {
                echo 'Error sending email: ' . $mailer->ErrorInfo;
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        echo '<div class="alert alert-danger">Email not registered.</div>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Bestlink College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .verification-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .brand-logo {
            width: 100px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <img src="assets/img/bcp.png" alt="School Logo" class="brand-logo">
            <h2 class="text-center mb-4">Email Verification</h2>
            <p class="text-center mb-4">Enter your registered email to receive verification code</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Verification Code</button>
            </form>
            
            <p class="text-center mt-3">
                Don't have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>