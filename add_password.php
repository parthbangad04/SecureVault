<?php
include "includes/auth.php";
include "config/database.php";
include "config/encryption.php";

$user_id = $_SESSION["user_id"];
$message = "";
$message_type = "";
$website_name = "";
$website_url = "";
$username = "";
$category = "";
$notes = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $website_name = trim($_POST["website_name"] ?? "");
    $website_url = trim($_POST["website_url"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $category = trim($_POST["category"] ?? "");
    $notes = trim($_POST["notes"] ?? "");

    if ($website_name === "" || $username === "" || $password === "") {
        $message = "Please fill all required fields.";
        $message_type = "error";
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $message = "Password must be between 8 and 20 characters.";
        $message_type = "error";
    } else {
        if ($website_url !== "") {
            if (!preg_match("/^https?:\/\//i", $website_url)) {
                $website_url = "https://" . $website_url;
            }

            if (!filter_var($website_url, FILTER_VALIDATE_URL)) {
                $message = "Please enter a valid website URL.";
                $message_type = "error";
            }
        }

        if ($message === "") {
            $encrypted_password = encryptPassword($password);

            if ($encrypted_password === false) {
                $message = "Password encryption failed.";
                $message_type = "error";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO passwords
                    (user_id, website_name, website_url, username, password, category, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                if (!$stmt) {
                    $message = "Database error: " . $conn->error;
                    $message_type = "error";
                } else {
                    $stmt->bind_param(
                        "issssss",
                        $user_id,
                        $website_name,
                        $website_url,
                        $username,
                        $encrypted_password,
                        $category,
                        $notes
                    );

                    if ($stmt->execute()) {
                        $message = "Password saved securely.";
                        $message_type = "success";

                        $website_name = "";
                        $website_url = "";
                        $username = "";
                        $category = "";
                        $notes = "";
                    } else {
                        $message = "Unable to save account.";
                        $message_type = "error";
                    }

                    $stmt->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Password - SecureVault</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f1f5f9;
    color: #1e293b;
    min-height: 100vh;
}

.navbar {
    height: 70px;
    background: linear-gradient(135deg, #0f172a, #1e3a8a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
}

.logo {
    font-size: 25px;
    font-weight: bold;
}

.logo span {
    color: #38bdf8;
}

.back-dashboard {
    color: white;
    text-decoration: none;
    font-size: 14px;
}

.back-dashboard:hover {
    color: #38bdf8;
}

.container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.page-title {
    margin-bottom: 25px;
}

.page-title h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.page-title p {
    color: #64748b;
}

.card {
    background: white;
    padding: 35px;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
}

.form-group {
    margin-bottom: 22px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #334155;
}

.required {
    color: #ef4444;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
    outline: none;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
}

textarea {
    min-height: 100px;
    resize: vertical;
}

.input-with-action {
    position: relative;
}

.input-with-action input {
    padding-right: 90px;
}

.open-link-btn {
    position: absolute;
    right: 7px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: #eff6ff;
    color: #2563eb;
    padding: 7px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
}

.open-link-btn:hover {
    background: #dbeafe;
}

.input-help {
    margin-top: 6px;
    color: #64748b;
    font-size: 12px;
}

.username-box {
    position: relative;
}

.username-box input {
    padding-right: 115px;
}

.email-btn {
    position: absolute;
    right: 7px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: #f0fdf4;
    color: #15803d;
    padding: 7px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
}

.email-btn:hover {
    background: #dcfce7;
}

.password-box {
    position: relative;
}

.password-box input {
    padding-right: 80px;
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

.password-strength {
    margin-top: 12px;
    display: none;
}

.strength-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 7px;
}

.strength-title {
    font-size: 13px;
    font-weight: bold;
    color: #475569;
}

.strength-label {
    font-size: 13px;
    font-weight: bold;
}

.strength-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.strength-progress {
    height: 100%;
    width: 0%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.strength-basic {
    background: #ef4444;
}

.strength-average {
    background: #f59e0b;
}

.strength-strong {
    background: #22c55e;
}

.strength-very-strong {
    background: #16a34a;
}

.password-info {
    margin-top: 10px;
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px;
}

.password-info-title {
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #334155;
}

.password-checks {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
}

.password-check {
    font-size: 12px;
    color: #94a3b8;
}

.password-check.valid {
    color: #16a34a;
}

.password-suggestion {
    margin-top: 9px;
    font-size: 12px;
    color: #64748b;
}

.generate-btn {
    margin-top: 10px;
    border: none;
    background: #e0f2fe;
    color: #0369a1;
    padding: 9px 13px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: bold;
}

.generate-btn:hover {
    background: #bae6fd;
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

.buttons {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.save-btn {
    flex: 1;
    border: none;
    background: #2563eb;
    color: white;
    padding: 13px;
    border-radius: 7px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

.save-btn:hover {
    background: #1d4ed8;
}

.cancel-btn {
    flex: 1;
    text-align: center;
    text-decoration: none;
    background: #e2e8f0;
    color: #334155;
    padding: 13px;
    border-radius: 7px;
    font-weight: bold;
}

.cancel-btn:hover {
    background: #cbd5e1;
}

@media (max-width: 600px) {
    .navbar {
        padding: 0 20px;
    }

    .logo {
        font-size: 20px;
    }

    .card {
        padding: 25px 20px;
    }

    .buttons {
        flex-direction: column;
    }

    .password-checks {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">
        🔐 Secure<span>Vault</span>
    </div>

    <a href="dashboard.php" class="back-dashboard">
        ← Back to Dashboard
    </a>
</nav>

<div class="container">

    <div class="page-title">
        <h1>Add New Account</h1>
        <p>Save your website login information securely.</p>
    </div>

    <div class="card">

        <?php if ($message !== ""): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="passwordForm">

            <div class="form-group">
                <label for="website_name">
                    Website Name
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="website_name"
                    name="website_name"
                    placeholder="Example: Amazon"
                    value="<?php echo htmlspecialchars($website_name); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="website_url">
                    Website URL
                </label>

                <div class="input-with-action">
                    <input
                        type="text"
                        id="website_url"
                        name="website_url"
                        placeholder="example.com or https://example.com"
                        value="<?php echo htmlspecialchars($website_url); ?>"
                    >

                    <button
                        type="button"
                        class="open-link-btn"
                        onclick="openWebsite()"
                    >
                        🔗 Open
                    </button>
                </div>

                <div class="input-help">
                    You can enter example.com or the complete https:// link.
                </div>
            </div>

            <div class="form-group">
                <label for="username">
                    Email / Username
                    <span class="required">*</span>
                </label>

                <div class="username-box">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="example@gmail.com"
                        value="<?php echo htmlspecialchars($username); ?>"
                        required
                    >

                    <button
                        type="button"
                        class="email-btn"
                        onclick="sendEmail()"
                    >
                        📧 Send Email
                    </button>
                </div>

                <div class="input-help">
                    If this is an email address, you can send an email directly.
                </div>
            </div>

            <div class="form-group">

                <label for="password">
                    Password
                    <span class="required">*</span>
                </label>

                <div class="password-box">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter 8-20 character password"
                        autocomplete="new-password"
                        minlength="8"
                        maxlength="20"
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

                <div class="password-strength" id="passwordStrength">

                    <div class="strength-header">
                        <span class="strength-title">
                            Password Strength
                        </span>

                        <span
                            class="strength-label"
                            id="strengthLabel"
                        >
                            Basic
                        </span>
                    </div>

                    <div class="strength-bar">
                        <div
                            class="strength-progress"
                            id="strengthProgress"
                        ></div>
                    </div>

                    <div class="password-info">

                        <div class="password-info-title">
                            Password requirements
                        </div>

                        <div class="password-checks">

                            <div
                                class="password-check"
                                id="checkLength"
                            >
                                ✗ 8-20 characters
                            </div>

                            <div
                                class="password-check"
                                id="checkUpper"
                            >
                                ✗ Uppercase letter
                            </div>

                            <div
                                class="password-check"
                                id="checkLower"
                            >
                                ✗ Lowercase letter
                            </div>

                            <div
                                class="password-check"
                                id="checkNumber"
                            >
                                ✗ Number
                            </div>

                            <div
                                class="password-check"
                                id="checkSpecial"
                            >
                                ✗ Special character
                            </div>

                        </div>

                        <div
                            class="password-suggestion"
                            id="passwordSuggestion"
                        >
                            Enter a password to check its strength.
                        </div>

                    </div>
                </div>

                <button
                    type="button"
                    class="generate-btn"
                    onclick="generatePassword()"
                >
                    🎲 Generate Strong Password
                </button>

            </div>

            <div class="form-group">

                <label for="category">
                    Category
                </label>

                <select
                    id="category"
                    name="category"
                >
                    <option value="">
                        Select Category
                    </option>

                    <option value="Social Media">
                        Social Media
                    </option>

                    <option value="Shopping">
                        Shopping
                    </option>

                    <option value="Banking">
                        Banking
                    </option>

                    <option value="Education">
                        Education
                    </option>

                    <option value="Work">
                        Work
                    </option>

                    <option value="Development">
                        Development
                    </option>

                    <option value="Entertainment">
                        Entertainment
                    </option>

                    <option value="Other">
                        Other
                    </option>
                </select>

            </div>

            <div class="form-group">

                <label for="notes">
                    Notes
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    placeholder="Add any additional information..."
                ><?php echo htmlspecialchars($notes); ?></textarea>

            </div>

            <div class="buttons">

                <a
                    href="dashboard.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="save-btn"
                >
                    🔐 Save Account
                </button>

            </div>

        </form>

    </div>

</div>

<script>

function togglePassword()
{
    const password =
        document.getElementById("password");

    const button =
        document.querySelector(".show-btn");

    if (password.type === "password") {
        password.type = "text";
        button.textContent = "Hide";
    } else {
        password.type = "password";
        button.textContent = "Show";
    }
}

const passwordInput =
    document.getElementById("password");

passwordInput.addEventListener(
    "input",
    checkPasswordStrength
);

function checkPasswordStrength()
{
    const password =
        passwordInput.value;

    const strengthBox =
        document.getElementById("passwordStrength");

    const progress =
        document.getElementById("strengthProgress");

    const label =
        document.getElementById("strengthLabel");

    const suggestion =
        document.getElementById("passwordSuggestion");

    if (password.length === 0) {
        strengthBox.style.display = "none";
        return;
    }

    strengthBox.style.display = "block";

    let score = 0;

    const hasLength =
        password.length >= 8 &&
        password.length <= 20;

    const hasUpper =
        /[A-Z]/.test(password);

    const hasLower =
        /[a-z]/.test(password);

    const hasNumber =
        /[0-9]/.test(password);

    const hasSpecial =
        /[^A-Za-z0-9]/.test(password);

    if (password.length >= 8) {
        score++;
    }

    if (password.length >= 12) {
        score++;
    }

    if (hasUpper) {
        score++;
    }

    if (hasLower) {
        score++;
    }

    if (hasNumber) {
        score++;
    }

    if (hasSpecial) {
        score++;
    }

    updateCheck(
        "checkLength",
        hasLength,
        "8-20 characters"
    );

    updateCheck(
        "checkUpper",
        hasUpper,
        "Uppercase letter"
    );

    updateCheck(
        "checkLower",
        hasLower,
        "Lowercase letter"
    );

    updateCheck(
        "checkNumber",
        hasNumber,
        "Number"
    );

    updateCheck(
        "checkSpecial",
        hasSpecial,
        "Special character"
    );

    progress.className =
        "strength-progress";

    if (password.length < 8) {

        label.textContent =
            "🔴 Basic";

        label.style.color =
            "#ef4444";

        progress.style.width =
            "25%";

        progress.classList.add(
            "strength-basic"
        );

        suggestion.textContent =
            "Password must contain at least 8 characters.";

    } else if (score <= 3) {

        label.textContent =
            "🟠 Average";

        label.style.color =
            "#f59e0b";

        progress.style.width =
            "50%";

        progress.classList.add(
            "strength-average"
        );

        suggestion.textContent =
            "Average password. Add uppercase, numbers and special characters.";

    } else if (score <= 5) {

        label.textContent =
            "🟢 Strong";

        label.style.color =
            "#16a34a";

        progress.style.width =
            "75%";

        progress.classList.add(
            "strength-strong"
        );

        suggestion.textContent =
            "Good password! Increase the length or add another character type.";

    } else if (password.length <= 20) {

        label.textContent =
            "🟢 Very Strong";

        label.style.color =
            "#15803d";

        progress.style.width =
            "100%";

        progress.classList.add(
            "strength-very-strong"
        );

        suggestion.textContent =
            "Excellent! This is a very strong password.";

    } else {

        label.textContent =
            "🔴 Invalid";

        label.style.color =
            "#ef4444";

        progress.style.width =
            "100%";

        progress.classList.add(
            "strength-basic"
        );

        suggestion.textContent =
            "Password cannot contain more than 20 characters.";
    }
}

function updateCheck(
    elementId,
    valid,
    text
)
{
    const element =
        document.getElementById(elementId);

    if (valid) {

        element.textContent =
            "✓ " + text;

        element.classList.add(
            "valid"
        );

    } else {

        element.textContent =
            "✗ " + text;

        element.classList.remove(
            "valid"
        );
    }
}

function generatePassword()
{
    const uppercase =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    const lowercase =
        "abcdefghijklmnopqrstuvwxyz";

    const numbers =
        "0123456789";

    const special =
        "!@#$%^&*()_+";

    const all =
        uppercase +
        lowercase +
        numbers +
        special;

    let password = "";

    password +=
        uppercase[
            Math.floor(
                Math.random() *
                uppercase.length
            )
        ];

    password +=
        lowercase[
            Math.floor(
                Math.random() *
                lowercase.length
            )
        ];

    password +=
        numbers[
            Math.floor(
                Math.random() *
                numbers.length
            )
        ];

    password +=
        special[
            Math.floor(
                Math.random() *
                special.length
            )
        ];

    for (
        let i = 4;
        i < 16;
        i++
    ) {

        password +=
            all[
                Math.floor(
                    Math.random() *
                    all.length
                )
            ];
    }

    password =
        password
            .split("")
            .sort(
                () => Math.random() - 0.5
            )
            .join("");

    passwordInput.value =
        password;

    passwordInput.type =
        "text";

    document.querySelector(
        ".show-btn"
    ).textContent =
        "Hide";

    checkPasswordStrength();
}

function openWebsite()
{
    let url =
        document.getElementById(
            "website_url"
        ).value.trim();

    if (url === "") {

        alert(
            "Please enter a website URL first."
        );

        return;
    }

    if (
        !/^https?:\/\//i.test(url)
    ) {

        url =
            "https://" + url;
    }

    try {

        const parsed =
            new URL(url);

        if (
            parsed.protocol !== "http:" &&
            parsed.protocol !== "https:"
        ) {

            alert(
                "Only HTTP and HTTPS websites are allowed."
            );

            return;
        }

        window.open(
            parsed.href,
            "_blank",
            "noopener,noreferrer"
        );

    } catch (error) {

        alert(
            "Please enter a valid website URL."
        );
    }
}

function sendEmail()
{
    const email =
        document.getElementById(
            "username"
        ).value.trim();

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {

        alert(
            "Please enter a valid email address first."
        );

        return;
    }

    const choice =
        confirm(
            "Choose your email method:\n\n" +
            "OK = Open Gmail\n" +
            "Cancel = Use Default Email App"
        );

    if (choice) {

        const gmailUrl =
            "https://mail.google.com/mail/?view=cm&fs=1&to=" +
            encodeURIComponent(email);

        window.open(
            gmailUrl,
            "_blank"
        );

    } else {

        window.location.href =
            "mailto:" +
            encodeURIComponent(email);
    }
}

document.getElementById(
    "passwordForm"
).addEventListener(
    "submit",
    function(event)
    {
        const password =
            passwordInput.value;

        const urlInput =
            document.getElementById(
                "website_url"
            );

        let url =
            urlInput.value.trim();

        if (
            password.length < 8 ||
            password.length > 20
        ) {

            event.preventDefault();

            alert(
                "Password must be between 8 and 20 characters."
            );

            passwordInput.focus();

            return;
        }

        if (
            url !== "" &&
            !/^https?:\/\//i.test(url)
        ) {

            url =
                "https://" + url;

            urlInput.value =
                url;
        }
    }
);

</script>

</body>
</html>