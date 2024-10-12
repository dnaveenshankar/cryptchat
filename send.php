<?php
// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

require __DIR__ . '/vendor/autoload.php';

// Include the database connection file
include 'db_connection.php';

function generateUniqueAccessKey($conn) {
    do {
        $accessKey = rand(1000, 9999);
        $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE access_key = ?");
        $stmt->bind_param("i", $accessKey);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0);

    return $accessKey;
}

function convertNumericKeyToBinary($numericKey) {
    $binaryKey = str_pad($numericKey, 32, "0", STR_PAD_LEFT); // Pad key with zeros
    return substr($binaryKey, 0, 32); // Ensure it's 32 bytes
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiverMail = htmlspecialchars($_POST["receiverMail"]);
    $securityKey = htmlspecialchars($_POST["securityKey"]);
    $text = htmlspecialchars($_POST["text"]);
    $senderMail = htmlspecialchars($_GET["mail"]);
    $errors = [];

    // Validate input
    if (empty($receiverMail) || empty($securityKey) || empty($text)) {
        $errors[] = "All fields are required.";
    }
    
    if (strlen($text) > 2000) {
        $errors[] = "Text cannot exceed 2000 characters.";
    }

    if (empty($errors)) {
        try {
            // Convert numeric security key to 32-byte binary key
            $binaryKey = convertNumericKeyToBinary($securityKey);
            
            // Encrypt the text using AES Algorithm
            $cipher = 'aes-256-cbc';
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
            $encryptedText = openssl_encrypt($text, $cipher, $binaryKey, 0, $iv);
            $encryptedText = base64_encode($iv . $encryptedText); // Encode for storage
            
            // Generate a unique access key
            $accessKey = generateUniqueAccessKey($conn);
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO messages (access_key, sender, receiver, security_key, encrypted_text, status) VALUES (?, ?, ?, ?, ?, 'Not Seen')");
            $stmt->bind_param("issss", $accessKey, $senderMail, $receiverMail, $securityKey, $encryptedText);
            $stmt->execute();
            $stmt->close();
            
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
                $mail->addAddress($receiverMail);

                // Content
                $mail->isHTML(true);
                $mail->Subject = "You have a new message!";
                $mail->Body    = "
                    <html>
                    <body>
                        <p>Hello,</p>
                        <p>$senderMail has sent you a message.</p>
                        <p>Access Key: <strong>$accessKey</strong></p>
                        <p>Security Key: <strong>$securityKey</strong></p>
                        <p>Use these keys to decrypt the message.</p>
                        <p>Regards,<br>CryptChat</p>
                    </body>
                    </html>
                ";

                $mail->send();
                
                // Redirect with alert
                echo "<script>
                    alert('Message Encrypted and Sent Successfully');
                    window.location.href = 'my_messages.php?mail=" . urlencode($senderMail) . "';
                </script>";
                
                exit();
                
            } catch (Exception $e) {
                $errors[] = "Failed to send email. Mailer Error: " . $mail->ErrorInfo;
            }
            
        } catch (Exception $e) {
            $errors[] = "Encryption failed: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Send Message</title>
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
            text-align: center;
            width: 100%;
            max-width: 600px;
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

        .form-group input, .form-group textarea {
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
            margin: 10px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #007bff;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Send a Message</h2>
        
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error) echo htmlspecialchars($error) . "<br>"; ?>
            </div>
        <?php } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?mail=" . urlencode($_GET["mail"]); ?>">
            <div class="form-group">
                <label for="receiverMail">Receiver Mail:</label>
                <input type="email" name="receiverMail" id="receiverMail" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="securityKey">Security Key:</label>
                <input type="text" name="securityKey" id="securityKey" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="text">Text:</label>
                <textarea name="text" id="text" class="form-control" rows="6" maxlength="2000" required></textarea>
            </div>

            <button type="submit" class="btn">Send</button>
            <a href="dashboard.php?mail=<?php echo urlencode($_GET["mail"]); ?>" class="btn btn-back">Back</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
