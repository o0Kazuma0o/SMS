<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = $_POST['verification_code'];
    if ($entered_code == $_SESSION['verification_code']) {
        header('Location: select_branch.php');
        exit;
    } else {
        $error_message = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - Bestlink College</title>
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

        .error-message {
            display: none;
            /* Hidden by default */
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c62828;
        }
    </style>
</head>
<body>
    <div style="position: absolute; top: 10px; left: 10px; z-index: 2;">
        <a href="verify_email.php" style="text-decoration: none; background-color: #1e3a8a; color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold;">
            &larr; Back
        </a>
    </div>
    <div>
        <div class="title text-center mb-10">
            <img src="assets/img/bcp.png" alt="School Logo" width="100">
            <h2 class="mt-3">Enter Verification Code</h2>
            <p class="text">Please enter the verification code sent to your email</p>
        </div>

        <div class="container mt-5">
            <div class="card">
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code" required>
                    </div>
                    <button type="submit" class="btn proceed-btn">Verify</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>