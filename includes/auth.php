<?php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tell browser and proxy NOT to cache this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check login
if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

?>