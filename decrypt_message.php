<?php
// Include the database connection file
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer autoload
require 'vendor/autoload.php';

// Function to convert the numeric key to a binary key
function convertNumericKeyToBinary($numericKey) {
    $binaryKey = str_pad($numericKey, 32, "0", STR_PAD_LEFT); // Pad key with zeros
    return substr($binaryKey, 0, 32); // Ensure it's 32 bytes
}

header('Content-Type: application/json');

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are present
    if (isset($_POST['access_key'], $_POST['security_key'], $_POST['email'])) {
        $accessKey = htmlspecialchars($_POST['access_key']);
        $securityKey = htmlspecialchars($_POST['security_key']);
        $email = htmlspecialchars($_POST['email']); // Ensure the email is provided
        
        // Fetch the message from the database
        $stmt = $conn->prepare("SELECT sender, encrypted_text, security_key, status FROM messages WHERE access_key = ? AND receiver = ?");
        $stmt->bind_param("ss", $accessKey, $email); // Fixed the parameter type for email
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($sender, $encryptedText, $dbSecurityKey, $currentStatus);
            $stmt->fetch();
            
            // Check if the provided security key matches the one in the database
            if ($dbSecurityKey !== $securityKey) {
                echo json_encode(['success' => false, 'error' => 'Wrong security key.']);
            } else {
                try {
                    // Decode the encrypted text from Base64 and decrypt it
                    $encryptedText = base64_decode($encryptedText);
                    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
                    $iv = substr($encryptedText, 0, $ivSize);
                    $encryptedText = substr($encryptedText, $ivSize);
                    
                    $binaryKey = convertNumericKeyToBinary($securityKey);
                    $decryptedMessage = openssl_decrypt($encryptedText, 'aes-256-cbc', $binaryKey, 0, $iv);
                    
                    if ($decryptedMessage === false) {
                        throw new Exception('Decryption failed.');
                    }

                    // Update the message status to 'Seen' only if it is not already 'Seen'
                    if ($currentStatus !== 'Seen') {
                        $stmtUpdate = $conn->prepare("UPDATE messages SET status = 'Seen' WHERE access_key = ?");
                        $stmtUpdate->bind_param("s", $accessKey); // Fixed the parameter type for access key
                        $stmtUpdate->execute();
                        $stmtUpdate->close();

                        // Send an alert to the sender only if the message is seen for the first time
                        if ($sender) {
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
                                $mail->addAddress($sender);

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
                                echo json_encode(['success' => false, 'error' => 'Failed to send notification email. Mailer Error: ' . $mail->ErrorInfo]);
                                exit();
                            }
                        }
                    }

                    // Return the decrypted message as JSON
                    echo json_encode(['success' => true, 'decrypted_message' => $decryptedMessage]);

                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => 'Decryption failed: ' . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid access key or receiver email.']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
