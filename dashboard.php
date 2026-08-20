<?php

include "includes/auth.php";
include "config/database.php";

$user_id = $_SESSION["user_id"];

$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $search_value = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT
            id,
            website_name,
            website_url,
            username,
            category,
            created_at
         FROM passwords
         WHERE user_id = ?
         AND (
            website_name LIKE ?
            OR username LIKE ?
            OR category LIKE ?
         )
         ORDER BY id DESC"
    );

    $stmt->bind_param(
        "isss",
        $user_id,
        $search_value,
        $search_value,
        $search_value
    );

} else {

    $stmt = $conn->prepare(
        "SELECT
            id,
            website_name,
            website_url,
            username,
            category,
            created_at
         FROM passwords
         WHERE user_id = ?
         ORDER BY id DESC"
    );

    $stmt->bind_param(
        "i",
        $user_id
    );

}

$stmt->execute();

$result = $stmt->get_result();

$total_accounts = 0;

$count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM passwords
     WHERE user_id = ?"
);

$count_stmt->bind_param(
    "i",
    $user_id
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();

$count_data = $count_result->fetch_assoc();

$total_accounts = $count_data["total"];

$count_stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Dashboard - SecureVault</title>

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

.nav-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.welcome {
    font-size: 14px;
}

.logout {
    color: white;
    text-decoration: none;
    background: #ef4444;
    padding: 9px 15px;
    border-radius: 6px;
    font-size: 14px;
}

.logout:hover {
    background: #dc2626;
}

.container {
    max-width: 1200px;
    margin: 35px auto;
    padding: 0 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h1 {
    font-size: 30px;
}

.header p {
    color: #64748b;
    margin-top: 6px;
}

.add-btn {
    background: #2563eb;
    color: white;
    text-decoration: none;
    padding: 12px 18px;
    border-radius: 7px;
    font-weight: bold;
}

.add-btn:hover {
    background: #1d4ed8;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    padding: 22px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.stat-card h3 {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 10px;
}

.stat-card strong {
    font-size: 28px;
    color: #1e3a8a;
}

.search-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-input {
    flex: 1;
    padding: 13px 15px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
    outline: none;
}

.search-input:focus {
    border-color: #2563eb;
}

.search-btn {
    border: none;
    background: #2563eb;
    color: white;
    padding: 0 22px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

.search-btn:hover {
    background: #1d4ed8;
}

.clear-btn {
    background: #e2e8f0;
    color: #334155;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 0 18px;
    border-radius: 7px;
    font-weight: bold;
}

.clear-btn:hover {
    background: #cbd5e1;
}

.search-result {
    margin-top: 12px;
    color: #64748b;
    font-size: 14px;
}

.table-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.table-header h2 {
    font-size: 20px;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8fafc;
    color: #475569;
    text-align: left;
    padding: 14px 18px;
    font-size: 13px;
}

td {
    padding: 15px 18px;
    border-top: 1px solid #e2e8f0;
    font-size: 14px;
}

.website {
    font-weight: bold;
    color: #1e293b;
}

.website-url {
    display: block;
    color: #64748b;
    font-size: 12px;
    margin-top: 4px;
}

.category {
    display: inline-block;
    background: #dbeafe;
    color: #1d4ed8;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.view-btn {
    background: #e0f2fe;
    color: #0369a1;
    text-decoration: none;
    padding: 7px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
}

.view-btn:hover {
    background: #bae6fd;
}

.empty {
    text-align: center;
    padding: 50px 20px;
    color: #64748b;
}

.empty-icon {
    font-size: 45px;
    margin-bottom: 12px;
}

.empty h3 {
    color: #334155;
    margin-bottom: 8px;
}

@media (max-width: 800px) {

    .navbar {
        padding: 0 20px;
    }

    .welcome {
        display: none;
    }

    .container {
        margin-top: 25px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .search-form {
        flex-direction: column;
    }

    .search-btn {
        padding: 12px;
    }

    .clear-btn {
        justify-content: center;
        padding: 12px;
    }

}

</style>

</head>

<body>

<nav class="navbar">

    <div class="logo">
        🔐 Secure<span>Vault</span>
    </div>

    <div class="nav-right">

        <span class="welcome">

            Welcome,

            <?php

            echo htmlspecialchars(
                $_SESSION["username"] ?? "User"
            );

            ?>

        </span>

        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<div class="container">


    <div class="header">

        <div>

            <h1>
                Dashboard
            </h1>

            <p>
                Manage your saved accounts securely.
            </p>

        </div>

       <div style="display:flex;gap:10px;">

    <a
        href="download_pdf.php"
        class="add-btn"
    >
        📄 Download PDF
    </a>

    <a
        href="add_password.php"
        class="add-btn"
    >
        + Add Password
    </a>

</div>
    </div>


    <div class="stats">

        <div class="stat-card">

            <h3>
                TOTAL ACCOUNTS
            </h3>

            <strong>
                <?php echo $total_accounts; ?>
            </strong>

        </div>


        <div class="stat-card">

            <h3>
                SEARCH RESULTS
            </h3>

            <strong>
                <?php echo $result->num_rows; ?>
            </strong>

        </div>


        <div class="stat-card">

            <h3>
                SECURITY
            </h3>

            <strong>
                🔐 Secure
            </strong>

        </div>

    </div>


    <div class="search-box">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="🔎 Search website, email or category..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button
                type="submit"
                class="search-btn"
            >
                Search
            </button>

            <?php if ($search !== ""): ?>

                <a
                    href="dashboard.php"
                    class="clear-btn"
                >
                    Clear
                </a>

            <?php endif; ?>

        </form>


        <?php if ($search !== ""): ?>

            <div class="search-result">

                Showing results for:

                <strong>
                    <?php echo htmlspecialchars($search); ?>
                </strong>

            </div>

        <?php endif; ?>

    </div>


    <div class="table-card">

        <div class="table-header">

            <h2>
                Saved Accounts
            </h2>

        </div>


        <?php if ($result->num_rows > 0): ?>

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Website
                            </th>

                            <th>
                                Email / Username
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Added
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <div class="website">

                                    <?php

                                    echo htmlspecialchars(
                                        $row["website_name"]
                                    );

                                    ?>

                                </div>

                                <?php if (!empty($row["website_url"])): ?>

                                    <span class="website-url">

                                        <?php

                                        echo htmlspecialchars(
                                            $row["website_url"]
                                        );

                                        ?>

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row["username"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php if (!empty($row["category"])): ?>

                                    <span class="category">

                                        <?php

                                        echo htmlspecialchars(
                                            $row["category"]
                                        );

                                        ?>

                                    </span>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    date(
                                        "d M Y",
                                        strtotime(
                                            $row["created_at"]
                                        )
                                    )
                                );

                                ?>

                            </td>


                            <td>

                                <a
                                    href="view_password.php?id=<?php echo $row["id"]; ?>"
                                    class="view-btn"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty">

                <div class="empty-icon">
                    🔍
                </div>

                <?php if ($search !== ""): ?>

                    <h3>
                        No accounts found
                    </h3>

                    <p>
                        Try another website, email or category.
                    </p>

                <?php else: ?>

                    <h3>
                        No accounts saved yet
                    </h3>

                    <p>
                        Click "Add Password" to save your first account.
                    </p>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>

<?php

$stmt->close();

?>