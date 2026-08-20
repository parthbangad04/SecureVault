<?php

include "includes/auth.php";
include "config/database.php";
include "config/encryption.php";

$user_id = $_SESSION["user_id"];

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: dashboard.php");
    exit();
}

$password_id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "SELECT
        id,
        website_name,
        website_url,
        username,
        password,
        category,
        notes
     FROM passwords
     WHERE id = ?
     AND user_id = ?"
);

$stmt->bind_param(
    "ii",
    $password_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: dashboard.php");
    exit();
}

$account = $result->fetch_assoc();

$stmt->close();

$website_name = $account["website_name"];
$website_url = $account["website_url"];
$username = $account["username"];
$category = $account["category"];
$notes = $account["notes"];

$decrypted_password =
    decryptPassword($account["password"]);

if ($decrypted_password === false) {
    $decrypted_password = "";
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $website_name =
        trim($_POST["website_name"]);

    $website_url =
        trim($_POST["website_url"]);

    $username =
        trim($_POST["username"]);

    $password =
        $_POST["password"];

    $category =
        trim($_POST["category"]);

    $notes =
        trim($_POST["notes"]);

    if (
        empty($website_name) ||
        empty($username) ||
        empty($password)
    ) {

        $message =
            "Please fill all required fields.";

        $message_type =
            "error";

    } else {

        $encrypted_password =
            encryptPassword($password);

        $update = $conn->prepare(
            "UPDATE passwords
             SET
                website_name = ?,
                website_url = ?,
                username = ?,
                password = ?,
                category = ?,
                notes = ?
             WHERE id = ?
             AND user_id = ?"
        );

        $update->bind_param(
            "ssssssii",
            $website_name,
            $website_url,
            $username,
            $encrypted_password,
            $category,
            $notes,
            $password_id,
            $user_id
        );

        if ($update->execute()) {

            header(
                "Location: view_password.php?id=" .
                $password_id
            );

            exit();

        } else {

            $message =
                "Unable to update account.";

            $message_type =
                "error";

        }

        $update->close();

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Edit Account - SecureVault
</title>

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

.back {
    color: white;
    text-decoration: none;
    font-size: 14px;
}

.back:hover {
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
    margin-bottom: 20px;
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
}

textarea {
    min-height: 100px;
    resize: vertical;
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

.generate-btn {
    margin-top: 8px;
    border: none;
    background: #e0f2fe;
    color: #0369a1;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
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

.error {
    background: #fee2e2;
    color: #991b1b;
}

.buttons {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.update-btn {
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

.update-btn:hover {
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

    .card {
        padding: 25px 20px;
    }

    .buttons {
        flex-direction: column;
    }

}

</style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        🔐 Secure<span>Vault</span>
    </div>

    <a
        href="dashboard.php"
        class="back"
    >
        ← Dashboard
    </a>

</nav>

<div class="container">

    <div class="page-title">

        <h1>
            Edit Account
        </h1>

        <p>
            Update your saved account information.
        </p>

    </div>

    <div class="card">

        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="">


            <div class="form-group">

                <label for="website_name">

                    Website Name

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="text"
                    id="website_name"
                    name="website_name"
                    value="<?php echo htmlspecialchars($website_name); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="website_url">

                    Website URL

                </label>

                <input
                    type="url"
                    id="website_url"
                    name="website_url"
                    value="<?php echo htmlspecialchars($website_url); ?>"
                >

            </div>


            <div class="form-group">

                <label for="username">

                    Email / Username

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($username); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">

                    Password

                    <span class="required">
                        *
                    </span>

                </label>

                <div class="password-box">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        value="<?php echo htmlspecialchars($decrypted_password); ?>"
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

                    <option
                        value="Social Media"
                        <?php
                        echo $category === "Social Media"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Social Media
                    </option>

                    <option
                        value="Shopping"
                        <?php
                        echo $category === "Shopping"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Shopping
                    </option>

                    <option
                        value="Banking"
                        <?php
                        echo $category === "Banking"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Banking
                    </option>

                    <option
                        value="Education"
                        <?php
                        echo $category === "Education"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Education
                    </option>

                    <option
                        value="Work"
                        <?php
                        echo $category === "Work"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Work
                    </option>

                    <option
                        value="Development"
                        <?php
                        echo $category === "Development"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Development
                    </option>

                    <option
                        value="Entertainment"
                        <?php
                        echo $category === "Entertainment"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Entertainment
                    </option>

                    <option
                        value="Other"
                        <?php
                        echo $category === "Other"
                            ? "selected"
                            : "";
                        ?>
                    >
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
                ><?php echo htmlspecialchars($notes); ?></textarea>

            </div>


            <div class="buttons">

                <a
                    href="view_password.php?id=<?php echo $password_id; ?>"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="update-btn"
                >
                    💾 Update Account
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

function generatePassword()
{

    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ" +
        "abcdefghijklmnopqrstuvwxyz" +
        "0123456789" +
        "!@#$%^&*()_+";

    let password = "";

    for (
        let i = 0;
        i < 16;
        i++
    ) {

        const randomIndex =
            Math.floor(
                Math.random() *
                characters.length
            );

        password +=
            characters[randomIndex];

    }

    document.getElementById(
        "password"
    ).value = password;

    document.getElementById(
        "password"
    ).type = "text";

    document.querySelector(
        ".show-btn"
    ).textContent = "Hide";

}

</script>

</body>

</html>