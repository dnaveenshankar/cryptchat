<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Adjust the path to autoload.php

// Include the database connection file
include 'db_connection.php';

$name = $userMail = $password = ""; // Renamed $mail to $userMail
$nameErr = $userMailErr = $passwordErr = ""; // Renamed $mailErr to $userMailErr
$message = '';
$email_status = '';

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate password
function validatePassword($password, $name) {
    $errors = [];
    if (strlen($password) < 6 || strlen($password) > 8) {
        $errors[] = "Password must be between 6 and 8 characters long";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match("/[@$!%*?&]/", $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    if (stripos($password, $name) !== false) {
        $errors[] = "Password must not contain your name";
    }
    return $errors;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitizeInput($_POST["name"]);
    $userMail = sanitizeInput($_POST["mail"]); // Updated to $userMail
    $password = sanitizeInput($_POST["password"]);

    // Validate Name
    if (empty($name)) {
        $nameErr = "Name is required";
    }

    // Validate Email
    if (empty($userMail)) {
        $userMailErr = "Mail ID is required"; // Updated to $userMailErr
    } else {
        // Check if email already exists
        $sql = "SELECT * FROM users WHERE mail='$userMail'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $userMailErr = "This Mail ID is already registered."; // Updated to $userMailErr
        }
    }

    // Validate Password
    if (empty($password)) {
        $passwordErr = "Password is required";
    } else {
        $passwordErrors = validatePassword($password, $name);
        if (!empty($passwordErrors)) {
            $passwordErr = implode("<br>", $passwordErrors);
        }
    }

    // If no errors, insert into database
    if (empty($nameErr) && empty($userMailErr) && empty($passwordErr)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, mail, password) VALUES ('$name', '$userMail', '$hashedPassword')";

        if ($conn->query($sql) === TRUE) {
           // Prepare PHPMailer
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host       = 'smtp.gmail.com'; // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true; // Enable SMTP authentication
    $mail->Username   = 'noreply.cryptchat@gmail.com'; // SMTP username
    $mail->Password   = 'xcpg qdnz jxim pnlt'; // Use the app password generated in Google Account
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('noreply.cryptchat@gmail.com', 'CryptChat');
    $mail->addAddress($userMail); // Ensure $userMail is a string

    // Attach the image
    $mail->addEmbeddedImage('cryptchat.png', 'logo'); // Provide the correct path to your local image

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = "Welcome to CryptChat!";
    $mail->Body    = '
        <html>
        <body>
            <div style="text-align: center;">
                <img src="cid:logo" alt="CryptChat Logo" style="width: 100px; height: auto;"/>
                <h2>Hello ' . htmlspecialchars($name) . ',</h2>
                <p>Thank you for registering with CryptChat! We are excited to have you on board.</p>
                <p>Your username is: ' . htmlspecialchars($userMail) . '</p>
                <p>Regards,<br>CryptChat</p>
            </div>
        </body>
        </html>
    ';

    $mail->send();
    $email_status = "A confirmation email has been sent to " . htmlspecialchars($userMail) . ".";
} catch (Exception $e) {
    $email_status = "Failed to send email. Mailer Error: " . $mail->ErrorInfo;
}

            // Redirect to dashboard.php with mail and name as query parameters
            header("Location: dashboard.php?mail=" . urlencode($userMail) . "&name=" . urlencode($name));
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0a1f2f; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #1a2a38; 
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .container h2 {
            margin-bottom: 20px;
            color: #e9ecef; 
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
            background-color: #28a745;
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
            background-color: #007bff;
        }

        .btn-home:hover {
            background-color: #0056b3;
        }

        .link {
            color: white; 
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="cryptchat.png" alt="CryptChat Logo">
        <h2>Connect Securely!</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?php echo $name; ?>" class="form-control">
                <div class="error"><?php echo $nameErr; ?></div>
            </div>

            <div class="form-group">
                <label for="mail">Mail ID:</label>
                <input type="email" name="mail" id="mail" value="<?php echo $userMail; ?>" class="form-control">
                <div class="error"><?php echo $userMailErr; ?></div>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control">
                <div class="error"><?php echo $passwordErr; ?></div>
            </div>

            <button type="submit" class="btn btn-block">Create Account</button>
        </form>

        <a href="index.php" class="btn btn-home btn-block mt-2">Home</a>
        <div class="text-center mt-2" style="color: white;">
            Already have an account? <a href="login.php" style="color: blue" class="link">Login</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
