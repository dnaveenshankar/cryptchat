<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer autoload
require 'vendor/autoload.php';
include 'db_connection.php';

// Initialize variables
$email = "";
$emailErr = "";
$resetStatus = "";

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate a 6-digit random security code
function generateSecurityCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST["email"]);

    // Validate Email
    if (empty($email)) {
        $emailErr = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT mail FROM users WHERE mail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // Generate a 6-digit security code
            $securityCode = generateSecurityCode();
            $expires = time() + 900; // 15 minutes from now

            // Save security code and expiration in the database
            $stmtUpdate = $conn->prepare("REPLACE INTO password_resets (email, security_code, expires) VALUES (?, ?, ?)");
            $stmtUpdate->bind_param("sii", $email, $securityCode, $expires);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            // Prepare PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'noreply.cryptchat@gmail.com';
                $mail->Password   = 'xcpg qdnz jxim pnlt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('noreply.cryptchat@gmail.com', 'CryptChat');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <html>
                    <body>
                        <p>Hello,</p>
                        <p>We received a request to reset your password. Here is your security code:</p>
                        <h2>$securityCode</h2>
                        <p>This code is valid for 15 minutes.</p>
                        <p>Regards,<br>CryptChat</p>
                    </body>
                    </html>
                ";

                $mail->send();
                $resetStatus = "A reset code has been sent to your email. Please check your inbox.";
                // Redirect to reset page with email
                header("Location: reset_password.php?email=" . urlencode($email));
exit();
            } catch (Exception $e) {
                $resetStatus = "Failed to send reset email. Mailer Error: " . $mail->ErrorInfo;
            }

            // Ensure that the statement is closed
            if ($stmt->errno) {
                $stmt->close();
            }
        } else {
            $emailErr = "No account found with this email.";
        }

        // Close the statement if it is still open
        if ($stmt && !$stmt->errno) {
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Forgot Password</title>
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
        <h2>Forgot Password</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo $email; ?>" class="form-control">
                <div class="error"><?php echo $emailErr; ?></div>
            </div>

            <button type="submit" class="btn btn-block">Send Reset Code</button>
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
