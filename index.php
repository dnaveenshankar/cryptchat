<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptChat - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0a1f2f;
            color: #e9ecef;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .main-container {
            border: 2px solid #007bff; 
            border-radius: 15px; 
            padding: 20px;
            margin: 20px;
            max-width: 500px; 
            background-color: #1a2a38;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        .main-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%; 
            margin-bottom: 20px;
        }

        .main-container h1 {
            font-size: 24px; 
            margin-bottom: 20px;
        }

        .btn {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px 20px;
            margin: 10px;
            text-align: center;
            transition: background-color 0.3s, transform 0.3s;
            display: inline-block;
        }

        .btn:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .btn-login {
            background-color: #007bff;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .btn-signup {
            background-color: #17a2b8;
        }

        .btn-signup:hover {
            background-color: #117a8b;
        }    

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <img src="cryptchat.png" alt="CryptChat Logo">
        <h1>Welcome to CryptChat - Connect Securely</h1>
        <div class="text-center">
            <a href="login.php" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="signup.php" class="btn btn-signup">
                <i class="fas fa-user-plus"></i> Sign Up
            </a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
