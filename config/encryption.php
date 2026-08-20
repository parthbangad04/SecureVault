<?php

define("ENCRYPTION_KEY", "SecureVault_MCA_Project_2026_Parth_9xK7pL2mQ8");

define("ENCRYPTION_METHOD", "AES-256-CBC");

function encryptPassword($password)
{
    $key = hash("sha256", ENCRYPTION_KEY, true);

    $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);

    $iv = openssl_random_pseudo_bytes($ivLength);

    $encrypted = openssl_encrypt(
        $password,
        ENCRYPTION_METHOD,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($encrypted === false) {
        return false;
    }

    return base64_encode($iv . $encrypted);
}

function decryptPassword($encryptedPassword)
{
    if (empty($encryptedPassword)) {
        return false;
    }

    $key = hash("sha256", ENCRYPTION_KEY, true);

    $data = base64_decode(
        $encryptedPassword,
        true
    );

    if ($data === false) {
        return false;
    }

    $ivLength =
        openssl_cipher_iv_length(
            ENCRYPTION_METHOD
        );

    if (strlen($data) <= $ivLength) {
        return false;
    }

    $iv = substr(
        $data,
        0,
        $ivLength
    );

    $encrypted = substr(
        $data,
        $ivLength
    );

    return openssl_decrypt(
        $encrypted,
        ENCRYPTION_METHOD,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
}

?>