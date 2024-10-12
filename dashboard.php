<?php
// Include the database connection file
include 'db_connection.php';

if (!isset($_GET['mail']) || empty($_GET['mail'])) {
    header("Location: login.php"); // Redirect to login if no email parameter
    exit();
}

$email = htmlspecialchars($_GET['mail']);

// Fetch user's name from the database
$sql = "SELECT name FROM users WHERE mail='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = htmlspecialchars($row['name']);
} else {
    $name = "User";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            text-align: center;
            width: 100%;
            max-width: 600px;
        }

        .container h2 {
            margin-bottom: 30px;
            color: #e9ecef;
        }

        .btn {
            background-color: #28a745; 
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            padding: 15px;
            margin: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-send {
            background-color: #007bff; 
        }

        .btn-send:hover {
            background-color: #0056b3;
        }

        .btn-receive {
            background-color: #17a2b8; 
        }

        .btn-receive:hover {
            background-color: #117a8b;
        }

        .btn-messages {
            background-color: #ffc107; 
        }

        .btn-messages:hover {
            background-color: #e0a800;
        }

        .btn-logout {
            background-color: #dc3545;
            margin-top: 20px;
        }

        .btn-logout:hover {
            background-color: #c82333;
        }

        .btn-info {
            background-color: #6c757d;
        }

        .btn-info:hover {
            background-color: #5a6268;
        }

        .info-section {
            margin-top: 30px;
        }

        .info-section h3 {
            margin-bottom: 20px;
            color: #e9ecef;
        }

        .info-section .card {
            background-color: #343a40;
            border: none;
            color: #e9ecef;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-section .card-header {
            background-color: #007bff;
            color: white;
        }

        .info-section .card-body {
            font-size: 16px;
        }

        .info-section .card-body i {
            margin-right: 10px;
            color: #28a745;
        }

        .info-section .card-body p {
            margin-bottom: 10px;
        }

        .info-section .card-body .highlight {
            color: #ffc107;
            font-weight: bold;
        }
        .info-section {
        margin-top: 30px;
    }

    .info-section .card {
        background-color: #343a40;
        border: none;
        color: #e9ecef;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .info-section .card-header {
        background-color: #007bff;
        color: white;
    }

    .info-section .card-body {
        font-size: 16px;
    }

    .info-section .card-body i {
        margin-right: 10px;
        color: #28a745;
    }

    .info-section .card-body p {
        margin-bottom: 10px;
    }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo $name; ?>!</h2>
        
        <a href="send.php?mail=<?php echo urlencode($email); ?>" class="btn btn-send">
            <i class="fas fa-paper-plane"></i> Send
        </a>

        <a href="receive.php?mail=<?php echo urlencode($email); ?>" class="btn btn-receive">
            <i class="fas fa-inbox"></i> Receive
        </a>

        <a href="my_messages.php?mail=<?php echo urlencode($email); ?>" class="btn btn-messages">
            <i class="fas fa-comments"></i> My Messages
        </a>

        <a href="#" class="btn btn-info" data-toggle="modal" data-target="#infoModal">
            <i class="fas fa-info-circle"></i> Learn How This Works
        </a>

        <a href="#" class="btn btn-logout" onclick="confirmLogout()">Logout</a>
    </div>

    <!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel">How Encryption and Decryption Work</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body info-section">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-lock"></i> Encryption
                    </div>
                    <div class="card-body">
                        <h5>1. What is AES-256-CBC?</h5>
                        <p><strong>AES-256-CBC</strong> stands for Advanced Encryption Standard with a 256-bit key in Cipher Block Chaining mode. It’s a very secure method of scrambling information to keep it safe.</p>
                        <p><i class="fas fa-key"></i> **Key**: A secret code used to lock and unlock the message.</p>
                        <p><i class="fas fa-lock"></i> **Lock**: Scrambles the message so that only someone with the right key can unscramble it.</p>

                        <h5>2. Why Do We Use This Algorithm?</h5>
                        <p>AES-256-CBC is like a super secret code that's hard to crack. It uses a 256-bit key, making it extremely secure. Think of it as a very strong lock for your secret message.</p>
                        <p><i class="fas fa-shield-alt"></i> **Security**: Protects your message from unauthorized access.</p>

                        <h5>3. Why Do We Encode Before Storing?</h5>
                        <p>Before storing a message, we <strong>encode</strong> it. This is like putting it into a special box. Encoding changes the message into a format that is safe and easy to store.</p>
                        <p><i class="fas fa-box"></i> **Encode**: Converts the message into a format that can be safely stored.</p>
                        <p><i class="fas fa-unlock-alt"></i> **Decode**: Converts it back into the original format when needed.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-unlock"></i> Decryption
                    </div>
                    <div class="card-body">
                        <h5>1. How Does Decryption Work?</h5>
                        <p>Decryption is the opposite of encryption. We use the same secret key to unlock the scrambled message, just like unlocking a box with the right key.</p>
                        <p><i class="fas fa-key"></i> **Unlock**: Use the key to unscramble the message.</p>
                        <p><i class="fas fa-box-open"></i> **Open Box**: Retrieve the original message from the encoded format.</p>

                        <h5>2. Why Is This Important?</h5>
                        <p>Encryption keeps your messages safe from prying eyes. Only people with the right key can read them. Decryption lets you get back to your original message whenever you need it.</p>
                        <p><i class="fas fa-lock"></i> **Safety**: Ensures that only authorized users can read your message.</p>
                        <p><i class="fas fa-key"></i> **Access**: Allows you to unlock and read the message when needed.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php?mail=<?php echo urlencode($email); ?>";
            }
        }
    </script>
</body>
</html>
