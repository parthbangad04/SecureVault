<?php

include "includes/auth.php";
include "config/database.php";
include "config/encryption.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Download PDF - SecureVault</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    min-height: 100vh;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    width: 450px;
    max-width: 90%;
    background: white;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}

.icon {
    text-align: center;
    font-size: 50px;
    margin-bottom: 15px;
}

h1 {
    text-align: center;
    color: #1e293b;
    margin-bottom: 10px;
}

.description {
    text-align: center;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    color: #334155;
    margin-bottom: 8px;
}

input {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 15px;
    outline: none;
}

input:focus {
    border-color: #2563eb;
}

.info {
    background: #eff6ff;
    color: #1e40af;
    padding: 12px;
    border-radius: 7px;
    margin-top: 15px;
    font-size: 13px;
    line-height: 1.5;
}

.buttons {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button,
.back-btn {
    flex: 1;
    padding: 13px;
    border-radius: 7px;
    font-weight: bold;
    font-size: 15px;
    text-align: center;
    text-decoration: none;
}

button {
    border: none;
    background: #2563eb;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.back-btn {
    background: #e2e8f0;
    color: #334155;
}

.back-btn:hover {
    background: #cbd5e1;
}

</style>

</head>

<body>

<div class="card">

    <div class="icon">
        🔐
    </div>

    <h1>
        Secure PDF Backup
    </h1>

    <p class="description">
        Create a password-protected PDF containing
        all your saved accounts.
    </p>

    <form method="POST">

        <label for="pdf_password">
            PDF Password
        </label>

        <input
            type="password"
            id="pdf_password"
            name="pdf_password"
            placeholder="Enter password for PDF"
            minlength="4"
            required
        >

        <div class="info">
            Remember this password. You will need it
            to open the downloaded PDF.
        </div>

        <div class="buttons">

            <a
                href="dashboard.php"
                class="back-btn"
            >
                Cancel
            </a>

            <button type="submit">
                🔒 Create PDF
            </button>

        </div>

    </form>

</div>

</body>

</html>

<?php

exit();

}

$pdf_password = $_POST["pdf_password"] ?? "";

if (strlen($pdf_password) < 4) {

    die("PDF password must contain at least 4 characters.");

}

require_once __DIR__ . "/fpdf/fpdf_protection.php";

class SecurePDF extends FPDF_Protection
{

    function TableRow($data, $widths, $height = 8)
    {

        $nb = 0;

        for ($i = 0; $i < count($data); $i++) {

            $nb = max(
                $nb,
                $this->NbLines(
                    $widths[$i],
                    $data[$i]
                )
            );

        }

        $height = 5 * $nb;

        if (
            $this->GetY() + $height >
            $this->PageBreakTrigger
        ) {

            $this->AddPage();

        }

        for ($i = 0; $i < count($data); $i++) {

            $x = $this->GetX();

            $y = $this->GetY();

            $this->Rect(
                $x,
                $y,
                $widths[$i],
                $height
            );

            $this->MultiCell(
                $widths[$i],
                5,
                $data[$i],
                0,
                "L"
            );

            $this->SetXY(
                $x + $widths[$i],
                $y
            );

        }

        $this->Ln($height);

    }

    function NbLines($width, $text)
    {

        $cw = $this->CurrentFont["cw"];

        if ($width == 0) {

            $width =
                $this->w -
                $this->rMargin -
                $this->x;

        }

        $wmax =
            ($width - 2 * $this->cMargin) *
            1000 /
            $this->FontSize;

        $s = str_replace(
            "\r",
            "",
            $text
        );

        $nb = strlen($s);

        if (
            $nb > 0 &&
            $s[$nb - 1] == "\n"
        ) {

            $nb--;

        }

        $sep = -1;

        $i = 0;

        $j = 0;

        $l = 0;

        $nl = 1;

        while ($i < $nb) {

            $c = $s[$i];

            if ($c == "\n") {

                $i++;

                $sep = -1;

                $j = $i;

                $l = 0;

                $nl++;

                continue;

            }

            if ($c == " ") {

                $sep = $i;

            }

            $l += $cw[$c];

            if ($l > $wmax) {

                if ($sep == -1) {

                    if ($i == $j) {

                        $i++;

                    }

                } else {

                    $i = $sep + 1;

                }

                $sep = -1;

                $j = $i;

                $l = 0;

                $nl++;

            } else {

                $i++;

            }

        }

        return $nl;

    }

}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT
        website_name,
        website_url,
        username,
        password,
        category,
        notes
     FROM passwords
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$accounts = [];

while ($row = $result->fetch_assoc()) {

    $decrypted_password =
        decryptPassword(
            $row["password"]
        );

    if ($decrypted_password === false) {

        $decrypted_password =
            "Unable to decrypt";

    }

    $accounts[] = [
        "website" =>
            $row["website_name"],

        "url" =>
            $row["website_url"],

        "username" =>
            $row["username"],

        "password" =>
            $decrypted_password,

        "category" =>
            $row["category"],

        "notes" =>
            $row["notes"]
    ];

}

$stmt->close();

$pdf = new SecurePDF(
    "L",
    "mm",
    "A4"
);

$pdf->SetProtection(
    [],
    $pdf_password
);

$pdf->SetCreator(
    "SecureVault"
);

$pdf->SetAuthor(
    "SecureVault"
);

$pdf->SetTitle(
    "SecureVault Password Backup"
);

$pdf->SetMargins(
    10,
    10,
    10
);

$pdf->SetAutoPageBreak(
    true,
    15
);

$pdf->AddPage();

$pdf->SetFont(
    "Arial",
    "B",
    20
);

$pdf->Cell(
    0,
    10,
    "SecureVault",
    0,
    1,
    "C"
);

$pdf->SetFont(
    "Arial",
    "",
    11
);

$pdf->Cell(
    0,
    7,
    "Password Backup",
    0,
    1,
    "C"
);

$pdf->Ln(5);

$pdf->SetFont(
    "Arial",
    "",
    8
);

$pdf->Cell(
    0,
    6,
    "Total Accounts: " .
    count($accounts),
    0,
    1,
    "L"
);

$pdf->Ln(3);

$widths = [
    38,
    55,
    52,
    45,
    30,
    55
];

$headers = [
    "Website",
    "Website URL",
    "Email / Username",
    "Password",
    "Category",
    "Notes"
];

$pdf->SetFont(
    "Arial",
    "B",
    8
);

$pdf->SetFillColor(
    230,
    238,
    250
);

for ($i = 0; $i < count($headers); $i++) {

    $pdf->Cell(
        $widths[$i],
        10,
        $headers[$i],
        1,
        0,
        "C",
        true
    );

}

$pdf->Ln();

$pdf->SetFont(
    "Arial",
    "",
    7
);

if (count($accounts) === 0) {

    $pdf->Cell(
        array_sum($widths),
        10,
        "No accounts found.",
        1,
        1,
        "C"
    );

} else {

    foreach ($accounts as $account) {

        $data = [
            $account["website"],
            $account["url"],
            $account["username"],
            $account["password"],
            $account["category"],
            $account["notes"]
        ];

        for ($i = 0; $i < count($data); $i++) {

            $data[$i] =
                str_replace(
                    [
                        "\r",
                        "\n"
                    ],
                    " ",
                    $data[$i]
                );

        }

        $pdf->TableRow(
            $data,
            $widths
        );

    }

}

$pdf->Ln(5);

$pdf->SetFont(
    "Arial",
    "I",
    7
);

$pdf->MultiCell(
    0,
    4,
    "This PDF contains sensitive password information. Keep it secure and do not share it with others.",
    0,
    "L"
);

$pdf->Output(
    "D",
    "SecureVault_Password_Backup.pdf"
);

exit();

?>