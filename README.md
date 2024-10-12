# CryptChat

CryptChat is a secure web application designed for sending encrypted messages with real-time email notifications. The messages are encrypted during transmission, and recipients need an access key along with a security key (shared personally by the sender) to decrypt and read the messages.

## Features

- **Encrypted Messaging**: Send encrypted messages to ensure privacy and security.
- **Real-Time Email Notifications**: Receive notifications with access keys immediately when a message is sent.
- **Secure Access**: Use an access key and a security key to decrypt and read messages.

## How It Works

1. **Sign Up**: Create an account to start sending and receiving encrypted messages.
2. **Send a Message**: When composing a message, it will be encrypted before being sent. The recipient will receive an email notification containing the access key.
3. **Receive a Message**: The recipient will use the access key from the email notification and the security key shared by the sender to decrypt and read the message.

## Getting Started

To start using CryptChat, follow these steps:

1. **Create an Account**: Sign up on the CryptChat portal.
2. **Verify Your Email**: Ensure you receive real-time notifications.
3. **Send a Message**: Use the message form to compose and send an encrypted message.
4. **Receive and Decrypt Messages**: Use the provided access and security keys to read your encrypted messages.

## Running the Application

To run CryptChat locally using XAMPP, follow these steps:

1. **Install XAMPP**: Download and install XAMPP from [here](https://www.apachefriends.org/index.html).
2. **Clone the Repository**: Clone the CryptChat repository to your local machine.
    ```sh
    git clone https://github.com/your-username/cryptchat.git
    ```
3. **Move to XAMPP's htdocs**: Copy the cloned repository to the `htdocs` folder in your XAMPP installation directory.
4. **Start XAMPP**: Open XAMPP Control Panel and start Apache and MySQL.
5. **Database Setup**: Create a new database for CryptChat using phpMyAdmin.
6. **Configure the Application**: Update the database configuration in the application's configuration file.
7. **Access CryptChat**: Open your browser and go to `http://localhost/cryptchat`.

## Contact

For any questions or feedback, please contact us at dnaveenshankar2003@gmail.com.
