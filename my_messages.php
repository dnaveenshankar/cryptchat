<?php
// Include the database connection file
include 'db_connection.php';

if (!isset($_GET['mail']) || empty($_GET['mail'])) {
    header("Location: login.php"); // Redirect to login if no email parameter
    exit();
}

$email = htmlspecialchars($_GET['mail']);

// Function to convert the numeric key to a binary key
function convertNumericKeyToBinary($numericKey) {
    $binaryKey = str_pad($numericKey, 32, "0", STR_PAD_LEFT); // Pad key with zeros
    return substr($binaryKey, 0, 32); // Ensure it's 32 bytes
}

// Fetch sent messages
$stmt_sent = $conn->prepare("SELECT * FROM messages WHERE sender = ?");
$stmt_sent->bind_param("s", $email);
$stmt_sent->execute();
$result_sent = $stmt_sent->get_result();

// Fetch received messages
$stmt_received = $conn->prepare("SELECT * FROM messages WHERE receiver = ?");
$stmt_received->bind_param("s", $email);
$stmt_received->execute();
$result_received = $stmt_received->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - My Messages</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0a1f2f;
            color: white;
            padding: 20px;
        }

        .container {
            background-color: #1a2a38;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            table-layout: fixed;
        }

        table th, table td {
            text-align: left;
            padding: 10px;
            word-wrap: break-word;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table td {
            background-color: #343a40;
            color: white;
        }

        .btn-decrypt {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 5px 10px;
            transition: background-color 0.3s;
        }

        .btn-decrypt:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .modal-content {
            background-color: #1a2a38; /* Match the page background */
            color: white;
        }

        .modal-body {
            text-align: center;
        }

        .modal-footer {
            justify-content: center;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Sent Messages</h2>
        <?php if ($result_sent->num_rows > 0) { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Access Key</th>
                        <th>Security Key</th>
                        <th>Sender</th>
                        <th>Message</th>
                        <th>Encrypted Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_sent->fetch_assoc()) { 
                        // Decrypt the message
                        $encryptedText = base64_decode($row['encrypted_text']);
                        $ivSize = openssl_cipher_iv_length('aes-256-cbc');
                        $iv = substr($encryptedText, 0, $ivSize);
                        $encryptedText = substr($encryptedText, $ivSize);

                        $binaryKey = convertNumericKeyToBinary($row['security_key']);
                        $decryptedMessage = openssl_decrypt($encryptedText, 'aes-256-cbc', $binaryKey, 0, $iv);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['access_key']); ?></td>
                            <td><?php echo htmlspecialchars($row['security_key']); ?></td>
                            <td><?php echo htmlspecialchars($row['sender']); ?></td>
                            <td><?php echo htmlspecialchars($decryptedMessage); ?></td>
                            <td><?php echo htmlspecialchars($row['encrypted_text']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No sent messages found.</p>
        <?php } ?>
    </div>

    <div class="container">
        <h2>Received Messages</h2>
        <?php if ($result_received->num_rows > 0) { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Access Key</th>
                        <th>Sender</th>
                        <th>Encrypted Message</th>
                        <th>Decrypt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_received->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['access_key']); ?></td>
                            <td><?php echo htmlspecialchars($row['sender']); ?></td>
                            <td><?php echo htmlspecialchars($row['encrypted_text']); ?></td>
                            <td>
                                <button class="btn-decrypt" onclick="showDecryptModal('<?php echo $row['access_key']; ?>', '<?php echo $row['encrypted_text']; ?>')">Decrypt</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No received messages found.</p>
        <?php } ?>
    </div>

    <a href="dashboard.php?mail=<?php echo urlencode($email); ?>" class="btn btn-back" style="display: block; width: fit-content; margin: 0 auto;">Back to Dashboard</a>


    <!-- Decrypt Modal -->
    <div id="decryptModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Decrypt Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalMessage"></div>
                    <form id="decryptForm" action="decrypt_message.php" method="POST">
                        <div class="form-group">
                            <label for="access_key">Access Key</label>
                            <input type="text" class="form-control" id="access_key" name="access_key" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="security_key">Security Key</label>
                            <input type="password" class="form-control" id="security_key" name="security_key" required>
                        </div>
                        <input type="hidden" id="encrypted_text" name="encrypted_text">
                        <input type="hidden" id="email" name="email" value="<?php echo $email; ?>">
                        <button type="submit" class="btn btn-primary">Decrypt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDecryptModal(accessKey, encryptedText) {
            document.getElementById('access_key').value = accessKey;
            document.getElementById('encrypted_text').value = encryptedText;
            document.getElementById('modalMessage').innerHTML = ''; // Clear previous messages
            $('#decryptModal').modal('show');
        }

        document.getElementById('decryptForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const form = event.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const modalMessage = document.getElementById('modalMessage');
                if (data.success) {
                    modalMessage.innerHTML = `<p>Decrypted Message: ${data.decrypted_message}</p>`;
                } else {
                    modalMessage.innerHTML = `<p class="text-danger">${data.error}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('modalMessage').innerHTML = `<p class="text-danger">An error occurred while processing your request.</p>`;
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
