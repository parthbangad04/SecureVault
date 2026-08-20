<?php

session_start();

include "config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                // Store user information in session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Invalid email or password.";
                $message_type = "error";
            }

        } else {

            $message = "Invalid email or password.";
            $message_type = "error";
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

    <title>Login - SecureVault</title>

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

        .login-container {
            width: 100%;
            max-width: 420px;
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

        .login-btn {
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

        .login-btn:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
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

        .back-home {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }

        .back-home:hover {
            color: #2563eb;
        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="logo">
        🔐 Secure<span>Vault</span>
    </div>

    <p class="subtitle">
        Login to your secure account
    </p>


    <?php if (!empty($message)): ?>

        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="">

        <div class="form-group">

            <label for="email">
                Email Address
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
            >

        </div>


        <div class="form-group">

            <label for="password">
                Master Password
            </label>

            <div class="password-box">

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your master password"
                    required
                >

                <button
                    type="button"
                    class="show-btn"
                    onclick="togglePassword()"
                >
                    Show
                </button>

            </div>

        </div>


        <button type="submit" class="login-btn">
            Login
        </button>

    </form>


    <div class="bottom-text">

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </div>


    <a href="index.php" class="back-home">
        ← Back to Home
    </a>

</div>


<script>

function togglePassword() {

    const field = document.getElementById("password");
    const button = document.querySelector(".show-btn");

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