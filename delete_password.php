<?php

include "includes/auth.php";
include "config/database.php";

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
    "SELECT id, website_name
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
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

$account = $result->fetch_assoc();

$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $delete = $conn->prepare(
        "DELETE FROM passwords
         WHERE id = ?
         AND user_id = ?"
    );

    $delete->bind_param(
        "ii",
        $password_id,
        $user_id
    );

    if ($delete->execute()) {

        $delete->close();

        header("Location: dashboard.php");
        exit();

    }

    $delete->close();

    $error = "Unable to delete the account.";

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

<title>Delete Account - SecureVault</title>

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
    max-width: 600px;
    margin: 80px auto;
    padding: 0 20px;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
    text-align: center;
}

.icon {
    font-size: 55px;
    margin-bottom: 20px;
}

h1 {
    font-size: 26px;
    margin-bottom: 12px;
}

.account-name {
    color: #2563eb;
    font-weight: bold;
}

.warning {
    color: #64748b;
    line-height: 1.6;
    margin: 20px 0 30px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.buttons {
    display: flex;
    gap: 12px;
}

.delete-btn,
.cancel-btn {
    flex: 1;
    padding: 13px;
    border-radius: 7px;
    text-decoration: none;
    font-weight: bold;
    font-size: 15px;
}

.delete-btn {
    border: none;
    background: #ef4444;
    color: white;
    cursor: pointer;
}

.delete-btn:hover {
    background: #dc2626;
}

.cancel-btn {
    background: #e2e8f0;
    color: #334155;
}

.cancel-btn:hover {
    background: #cbd5e1;
}

@media (max-width: 600px) {

    .navbar {
        padding: 0 20px;
    }

    .card {
        padding: 30px 20px;
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

    <div class="card">

        <div class="icon">
            ⚠️
        </div>

        <h1>
            Delete Account?
        </h1>

        <p class="warning">

            Are you sure you want to delete

            <span class="account-name">

                <?php

                echo htmlspecialchars(
                    $account["website_name"]
                );

                ?>

            </span>

            ?

            <br>

            This action cannot be undone.

        </p>

        <?php if (isset($error)): ?>

            <div class="error">

                <?php

                echo htmlspecialchars($error);

                ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="buttons">

                <a
                    href="view_password.php?id=<?php echo $password_id; ?>"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="delete-btn"
                >
                    🗑️ Delete Account
                </button>

            </div>

        </form>

    </div>

</div>

</body>

</html>