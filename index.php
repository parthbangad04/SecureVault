<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SecureVault - Personal Password Manager</title>

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
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 90%;
            max-width: 900px;
            text-align: center;
        }

        .logo {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .logo span {
            color: #38bdf8;
        }

        h1 {
            font-size: 38px;
            margin-bottom: 15px;
        }

        .description {
            font-size: 18px;
            line-height: 1.6;
            color: #dbeafe;
            max-width: 650px;
            margin: 0 auto 35px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            padding: 13px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .login {
            background: #38bdf8;
            color: #0f172a;
        }

        .register {
            border: 2px solid #38bdf8;
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
        }

        .features {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature {
            background: rgba(255, 255, 255, 0.08);
            padding: 25px 15px;
            border-radius: 12px;
        }

        .feature h3 {
            margin-bottom: 10px;
        }

        .feature p {
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 700px) {
            h1 {
                font-size: 30px;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="logo">
        🔐 Secure<span>Vault</span>
    </div>

    <h1>Your Personal Password Manager</h1>

    <p class="description">
        Securely manage your website accounts, emails, usernames,
        and passwords from one simple and organized location.
    </p>

    <div class="buttons">
        <a href="login.php" class="btn login">Login</a>
        <a href="register.php" class="btn register">Create Account</a>
    </div>

    <div class="features">

        <div class="feature">
            <h3>🔐 Secure</h3>
            <p>
                Keep your important account information protected
                behind your personal login.
            </p>
        </div>

        <div class="feature">
            <h3>🔎 Easy Search</h3>
            <p>
                Quickly find the website, email or account
                information you need.
            </p>
        </div>

        <div class="feature">
            <h3>📄 PDF Export</h3>
            <p>
                Export your saved account information into a
                PDF when required.
            </p>
        </div>

    </div>

</div>

</body>
</html>