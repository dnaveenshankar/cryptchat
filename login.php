<?php
// Include the database connection file
include 'db_connection.php';

$email = $password = "";
$emailErr = $passwordErr = "";
$login_status = '';

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST["mail"]);
    $password = sanitizeInput($_POST["password"]);

    // Validate Email
    if (empty($email)) {
        $emailErr = "Mail ID is required";
    }

    // Validate Password
    if (empty($password)) {
        $passwordErr = "Password is required";
    }

    // If no errors, check user credentials
    if (empty($emailErr) && empty($passwordErr)) {
        $sql = "SELECT * FROM users WHERE mail='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Redirect to dashboard.php with email as a query parameter
                header("Location: dashboard.php?mail=" . urlencode($email));
                exit();
            } else {
                $passwordErr = "Invalid password";
            }
        } else {
            $emailErr = "No account found with this Mail ID";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Login</title>
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

        .container img {
            width: 120px;
            height: 120px;
            border-radius: 50%; /* Round logo */
            margin-bottom: 20px;
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
        <img src="cryptchat.png" alt="CryptChat Logo">
        <h2>Connect Securely!</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="mail">Mail ID:</label>
                <input type="email" name="mail" id="mail" value="<?php echo $email; ?>" class="form-control">
                <div class="error"><?php echo $emailErr; ?></div>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control">
                <div class="error"><?php echo $passwordErr; ?></div>
            </div>

            <button type="submit" class="btn btn-block">Login</button>
        </form>

        <a href="index.php" class="btn btn-home btn-block mt-2">Home</a>
        <div class="text-center mt-2" style="color: white;">
            Don't have an account? <a href="signup.php" style="color: blue" class="link">Sign Up</a>
        </div>
        <div class="text-center mt-2" style="color: white;">
    Forgot your password? <a href="forgot_password.php" style="color: blue" class="link">Reset Password</a>
</div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
