<?php
include 'db_connection.php';

// Initialize variables
$email = "";
$securityCode = "";
$newPassword = "";
$emailErr = $securityCodeErr = $newPasswordErr = $resetStatus = "";

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Retrieve email from GET parameters
if (isset($_GET['email']) ) {
    $email = sanitizeInput($_GET['email']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST["email"]);
    $securityCode = sanitizeInput($_POST["security_code"]);
    $newPassword = sanitizeInput($_POST["new_password"]);

    // Validate Security Code
    if (empty($securityCode)) {
        $securityCodeErr = "Security code is required";
    }

    // Validate New Password
    if (empty($newPassword)) {
        $newPasswordErr = "New password is required";
    } elseif (strlen($newPassword) < 6) {
        $newPasswordErr = "Password must be at least 6 characters long";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // If no errors, validate security code and update password
    if (empty($securityCodeErr) && empty($newPasswordErr)) {
        // Check security code and expiration
        $stmt = $conn->prepare("SELECT expires FROM password_resets WHERE email = ? AND security_code = ?");
        $stmt->bind_param("si", $email, $securityCode);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($expires);
            $stmt->fetch();

            if (time() <= $expires) {
                // Security code is valid, proceed to update the password
                $stmtUpdate = $conn->prepare("UPDATE users SET password = ? WHERE mail = ?");
                $stmtUpdate->bind_param("ss", $hashedPassword, $email);
                if ($stmtUpdate->execute()) {
                    // Password updated, delete reset record
                    $stmtDelete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                    $stmtDelete->bind_param("s", $email);
                    $stmtDelete->execute();

                    $resetStatus = "Your password has been successfully reset. You can now log in.";
                    echo "<script>alert('$resetStatus');window.location.href='index.php';</script>";
                } else {
                    $resetStatus = "Failed to update password. Please try again.";
                }
                $stmtUpdate->close();
            } else {
                $securityCodeErr = "Security code has expired.";
            }
        } else {
            $securityCodeErr = "Invalid security code.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0a1f2f; /* Dark blue background for a cyber security feel */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #1a2a38; /* Slightly lighter blue for container */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
            color: #e9ecef; /* Light text color */
        }

        .form-group {
            text-align: left;
        }

        .form-group label {
            color: #e9ecef;
        }

        .form-group input {
            border: 1px solid #e9ecef;
            border-radius: 5px;
        }

        .form-group .error {
            color: #dc3545;
        }

        .btn {
            background-color: #28a745; /* Vibrant green for buttons */
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #218838;
        }

        .btn-home {
            background-color: #007bff; /* Vibrant blue for Home button */
        }

        .btn-home:hover {
            background-color: #0056b3;
        }

        .link {
            color: white; /* White color for login link */
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo $email; ?>" class="form-control" readonly>
            </div>
            
            <div class="form-group">
                <label for="security_code">Security Code:</label>
                <input type="text" name="security_code" id="security_code" value="<?php echo $securityCode; ?>" class="form-control">
                <div class="error"><?php echo $securityCodeErr; ?></div>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" class="form-control">
                <div class="error"><?php echo $newPasswordErr; ?></div>
            </div>

            <button type="submit" class="btn btn-block">Change Password</button>
        </form>

        <div class="mt-3">
            <?php echo $resetStatus; ?>
        </div>

        <a href="index.php" class="btn btn-home btn-block mt-2">Home</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
