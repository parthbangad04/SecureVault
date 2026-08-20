<?php

include "config/encryption.php";

$original = "Test@12345";

$encrypted = encryptPassword($original);

$decrypted = decryptPassword($encrypted);

echo "<h2>SecureVault Encryption Test</h2>";

echo "<p>Original:</p>";
echo "<input type='text' value='" . htmlspecialchars($original) . "' style='width:500px;'>";

echo "<p>Encrypted:</p>";
echo "<textarea style='width:500px;height:100px;'>" . htmlspecialchars($encrypted) . "</textarea>";

echo "<p>Decrypted:</p>";
echo "<input type='text' value='" . htmlspecialchars($decrypted) . "' style='width:500px;'>";

?>