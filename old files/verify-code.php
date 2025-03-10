<?php
session_start();
require('database.php');

if (!isset($_SESSION['email'])) {
    header('Location: email-verification.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = $_POST['verification_code'];
    
    $email = $_SESSION['email'];
    $query = "SELECT verification_code FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($user['verification_code'] == $verification_code) {
            // Update user as verified
            $update_query = "UPDATE users SET email_verified = 1 WHERE email = '$email'";
            mysqli_query($conn, $update_query);
            
            $_SESSION['verified'] = true;
            header('Location: select_branch.php');
            exit;
        } else {
            echo '<div class="alert alert-danger">Invalid verification code.</div>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - Bestlink College</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="text-center mb-4">Verify Code</h2>
            <p class="text-center mb-4">Enter the verification code sent to your email</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="verification_code" class="form-label">Verification Code</label>
                    <input type="number" class="form-control" id="verification_code" name="verification_code" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>