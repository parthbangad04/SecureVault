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
        notes,
        created_at,
        updated_at
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

$decrypted_password = decryptPassword(
    $account["password"]
);

if ($decrypted_password === false) {
    $decrypted_password = "";
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
View Account - SecureVault
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
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    color: white;
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

.card {
    background: white;
    border-radius: 14px;
    padding: 35px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
}

.website-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}

.website-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    border-radius: 12px;
    font-size: 28px;
}

.website-header h1 {
    font-size: 26px;
    margin-bottom: 5px;
}

.website-header p {
    color: #64748b;
    font-size: 14px;
}

.field {
    margin-bottom: 20px;
}

.field-label {
    display: block;
    font-weight: bold;
    color: #64748b;
    font-size: 13px;
    margin-bottom: 7px;
}

.field-value {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 12px;
    border-radius: 7px;
    min-height: 44px;
    word-break: break-word;
}

.password-row {
    display: flex;
    gap: 10px;
}

.password-row .field-value {
    flex: 1;
}

.password-button {
    border: none;
    background: #e0f2fe;
    color: #0369a1;
    padding: 0 15px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

.password-button:hover {
    background: #bae6fd;
}

.copy-button {
    margin-top: 8px;
    border: none;
    background: #f1f5f9;
    color: #334155;
    padding: 7px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.copy-button:hover {
    background: #e2e8f0;
}

.category {
    display: inline-block;
    background: #dbeafe;
    color: #1d4ed8;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

.notes {
    white-space: pre-wrap;
}

.actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #e2e8f0;
}

.action-button {
    flex: 1;
    text-align: center;
    text-decoration: none;
    padding: 12px;
    border-radius: 7px;
    font-weight: bold;
}

.edit {
    background: #2563eb;
    color: white;
}

.edit:hover {
    background: #1d4ed8;
}

.dashboard {
    background: #e2e8f0;
    color: #334155;
}

.dashboard:hover {
    background: #cbd5e1;
}

.website-link {
    color: #2563eb;
    text-decoration: none;
}

.website-link:hover {
    text-decoration: underline;
}

.delete {
    background: #fee2e2;
    color: #dc2626;
}

.delete:hover {
    background: #fecaca;
}

@media (max-width: 600px) {

    .navbar {
        padding: 0 20px;
    }

    .card {
        padding: 25px 20px;
    }

    .actions {
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

    <div class="card">

        <div class="website-header">

            <div class="website-icon">
                🌐
            </div>

            <div>

                <h1>

                    <?php

                    echo htmlspecialchars(
                        $account["website_name"]
                    );

                    ?>

                </h1>

                <p>
                    Saved account information
                </p>

            </div>

        </div>


        <div class="field">

            <span class="field-label">
                WEBSITE URL
            </span>

            <div class="field-value">

                <?php

                if (!empty($account["website_url"])):

                ?>

                    <a
                        href="<?php echo htmlspecialchars($account["website_url"]); ?>"
                        target="_blank"
                        class="website-link"
                    >

                        <?php

                        echo htmlspecialchars(
                            $account["website_url"]
                        );

                        ?>

                    </a>

                <?php else: ?>

                    Not provided

                <?php endif; ?>

            </div>

        </div>


        <div class="field">

            <span class="field-label">
                EMAIL / USERNAME
            </span>

            <div class="field-value">

                <?php

                echo htmlspecialchars(
                    $account["username"]
                );

                ?>

            </div>

            <button
                class="copy-button"
                onclick="copyText(
                    <?php
                    echo json_encode(
                        $account["username"]
                    );
                    ?>
                )"
            >

                📋 Copy Username

            </button>

        </div>


        <div class="field">

            <span class="field-label">
                PASSWORD
            </span>

            <div class="password-row">

                <div
                    class="field-value"
                    id="passwordField"
                >
                    ••••••••••••••••
                </div>

                <button
                    class="password-button"
                    onclick="togglePassword()"
                    id="passwordButton"
                >
                    Show
                </button>

            </div>

            <button
                class="copy-button"
                onclick="copyPassword()"
            >
                📋 Copy Password
            </button>

        </div>


        <div class="field">

            <span class="field-label">
                CATEGORY
            </span>

            <?php if (!empty($account["category"])): ?>

                <span class="category">

                    <?php

                    echo htmlspecialchars(
                        $account["category"]
                    );

                    ?>

                </span>

            <?php else: ?>

                <div class="field-value">
                    No category
                </div>

            <?php endif; ?>

        </div>


        <div class="field">

            <span class="field-label">
                NOTES
            </span>

            <div class="field-value notes">

                <?php

                if (!empty($account["notes"])) {

                    echo htmlspecialchars(
                        $account["notes"]
                    );

                } else {

                    echo "No notes";

                }

                ?>

            </div>

        </div>


        <div class="field">

            <span class="field-label">
                CREATED
            </span>

            <div class="field-value">

                <?php

                echo htmlspecialchars(
                    $account["created_at"]
                );

                ?>

            </div>

        </div>


<div class="actions">

    <a
        href="dashboard.php"
        class="action-button dashboard"
    >
        ← Back to Dashboard
    </a>

    <a
        href="edit_password.php?id=<?php echo $account["id"]; ?>"
        class="action-button edit"
    >
        ✏️ Edit Account
    </a>

    <a
        href="delete_password.php?id=<?php echo $account["id"]; ?>"
        class="action-button delete"
    >
        🗑️ Delete
    </a>

</div>

    </div>

</div>


<script>

const actualPassword =
    <?php

    echo json_encode(
        $decrypted_password
    );

    ?>;


function togglePassword()
{

    const field =
        document.getElementById(
            "passwordField"
        );

    const button =
        document.getElementById(
            "passwordButton"
        );

    if (
        field.textContent ===
        "••••••••••••••••"
    ) {

        field.textContent =
            actualPassword;

        button.textContent =
            "Hide";

    } else {

        field.textContent =
            "••••••••••••••••";

        button.textContent =
            "Show";

    }

}


function copyPassword()
{

    navigator.clipboard.writeText(
        actualPassword
    );

    alert(
        "Password copied to clipboard!"
    );

}


function copyText(text)
{

    navigator.clipboard.writeText(
        text
    );

    alert(
        "Username copied to clipboard!"
    );

}

</script>

</body>

</html>