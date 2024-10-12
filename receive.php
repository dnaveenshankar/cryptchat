<?php
// Include the database connection file
include 'db_connection.php';
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer autoload
require 'vendor/autoload.php';

function convertNumericKeyToBinary($numericKey) {
    $binaryKey = str_pad($numericKey, 32, "0", STR_PAD_LEFT); // Pad key with zeros
    return substr($binaryKey, 0, 32); // Ensure it's 32 bytes
}

if (!isset($_GET['mail']) || empty($_GET['mail'])) {
    header("Location: login.php"); // Redirect to login if no email parameter
    exit();
}

$email = htmlspecialchars($_GET['mail']);
$errors = [];
$decryptedMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accessKey = htmlspecialchars($_POST["accessKey"]);
    $securityKey = htmlspecialchars($_POST["securityKey"]);

    // Validate input
    if (empty($accessKey) || empty($securityKey)) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        // Fetch message from database
        $stmt = $conn->prepare("SELECT sender, encrypted_text, security_key, status FROM messages WHERE access_key = ? AND receiver = ?");
        $stmt->bind_param("is", $accessKey, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($sender, $encryptedText, $dbSecurityKey, $status);
            $stmt->fetch();
            
            // Check if security key matches
            if ($dbSecurityKey !== $securityKey) {
                $errors[] = "Wrong security key.";
            } else {
                try {
                    // Decode from Base64 and decrypt the text
                    $encryptedText = base64_decode($encryptedText);
                    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
                    $iv = substr($encryptedText, 0, $ivSize);
                    $encryptedText = substr($encryptedText, $ivSize);
                    
                    $binaryKey = convertNumericKeyToBinary($securityKey);
                    $decryptedMessage = openssl_decrypt($encryptedText, 'aes-256-cbc', $binaryKey, 0, $iv);
                    
                    if ($decryptedMessage === false) {
                        throw new Exception('Decryption failed.');
                    }

                    // Update message status to 'Seen' if not already seen
                    if ($status !== 'Seen') {
                        $stmtUpdate = $conn->prepare("UPDATE messages SET status = 'Seen' WHERE access_key = ?");
                        $stmtUpdate->bind_param("i", $accessKey);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();

                        // Send alert to sender
                        $stmtSender = $conn->prepare("SELECT sender FROM messages WHERE access_key = ?");
                        $stmtSender->bind_param("i", $accessKey);
                        $stmtSender->execute();
                        $stmtSender->bind_result($senderMail);
                        $stmtSender->fetch();
                        $stmtSender->close();

                        if ($senderMail) {
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
                                $mail->addAddress($senderMail);

                                // Content
                                $mail->isHTML(true);
                                $mail->Subject = "Message Seen Notification";
                                $mail->Body    = "
                                    <html>
                                    <body>
                                        <p>Hello,</p>
                                        <p>Your message with access key <strong>$accessKey</strong> has been seen by <strong>$email</strong>.</p>
                                        <p>Regards,<br>CryptChat</p>
                                    </body>
                                    </html>
                                ";

                                $mail->send();
                                
                            } catch (Exception $e) {
                                $errors[] = "Failed to send notification email. Mailer Error: " . $mail->ErrorInfo;
                            }
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = "Decryption failed: " . $e->getMessage();
                }
            }
        } else {
            $errors[] = "Invalid access key or receiver email.";
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
    <title>CryptChat - Receive Message</title>
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

        .form-group label {
            color: #e9ecef;
        }

        .form-group input {
            border: 1px solid #e9ecef;
            border-radius: 5px;
        }

        .btn {
            background-color: #28a745; 
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px;
            margin-top: 10px;
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

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Receive a Message</h2>
        
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error) echo htmlspecialchars($error) . "<br>"; ?>
            </div>
        <?php } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?mail=" . urlencode($_GET["mail"]); ?>">
            <div class="form-group">
                <label for="accessKey">Access Key:</label>
                <input type="text" name="accessKey" id="accessKey" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="securityKey">Security Key:</label>
                <input type="text" name="securityKey" id="securityKey" class="form-control" required>
            </div>

            <button type="submit" class="btn">Get Message</button>
            <a href="dashboard.php?mail=<?php echo urlencode($_GET["mail"]); ?>" class="btn btn-back">Back</a>
        </form>

        <?php if (!empty($decryptedMessage)) { ?>
            <div class="alert alert-success mt-4">
                <h4>Message:</h4>
                <p><?php echo nl2br(htmlspecialchars($decryptedMessage)); ?></p>
            </div>
        <?php } ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
