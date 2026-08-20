<?php

include "config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check empty fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {

        $message = "Please fill all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";
        $message_type = "error";

    } else {

        // Check whether email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

        } else {

            // Securely hash master password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Account created successfully! You can now login.";
                $message_type = "success";

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account - SecureVault</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .logo {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .logo span {
            color: #2563eb;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
        }

        .password-box {
            position: relative;
        }

        .password-box input {
            padding-right: 70px;
        }

        .show-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #2563eb;
            cursor: pointer;
            font-weight: bold;
        }

        .register-btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
        }

        .register-btn:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .bottom-text {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
        }

        .bottom-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="register-container">

    <div class="logo">
        🔐 Secure<span>Vault</span>
    </div>

    <p class="subtitle">
        Create your secure account
    </p>

    <?php if (!empty($message)): ?>

        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="">

        <div class="form-group">

            <label for="name">Full Name</label>

            <input
                type="text"
                id="name"
                name="name"
                placeholder="Enter your name"
                required
            >

        </div>


        <div class="form-group">

            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
            >

        </div>


        <div class="form-group">

            <label for="password">Master Password</label>

            <div class="password-box">

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 8 characters"
                    required
                >

                <button
                    type="button"
                    class="show-btn"
                    onclick="togglePassword('password', this)"
                >
                    Show
                </button>

            </div>

        </div>


        <div class="form-group">

            <label for="confirm_password">Confirm Password</label>

            <div class="password-box">

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    required
                >

                <button
                    type="button"
                    class="show-btn"
                    onclick="togglePassword('confirm_password', this)"
                >
                    Show
                </button>

            </div>

        </div>


        <button type="submit" class="register-btn">
            Create Account
        </button>

    </form>


    <div class="bottom-text">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>

</div>


<script>

function togglePassword(fieldId, button) {

    const field = document.getElementById(fieldId);

    if (field.type === "password") {

        field.type = "text";
        button.textContent = "Hide";

    } else {

        field.type = "password";
        button.textContent = "Show";

    }

}

</script>

</body>

</html>