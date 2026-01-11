<?php
global $schoolIcons, $schoolIconsOptions;

session_start();

// Prüfe, ob der Benutzer angemeldet und ein Admin ist
if (!isset($_SESSION['angemeldet']) || !in_array($_SESSION['rolle'], ['lehrer', 'admin'])) {
    header("Location: ../../dashboard.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../includes/icons.php";

$message = "";

$pageTitle = 'Fach hinzufügen';
$extraHead = <<<HTML
<style>
    .form-title { text-align: center; font-size: 28px; margin-top: 30px; color: steelblue; }
    .success { text-align: center; color: green; font-weight: bold; }
    .error { text-align: center; color: red; font-weight: bold; }
    .form-container { max-width: 450px; margin: 30px auto; padding: 25px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,0.12); }
    .form-container form { display: flex; flex-direction: column; gap: 18px; }
    .form-container label { font-size: 16px; font-weight: bold; color: #444; }
    .input-field { padding: 12px; border: 1px solid #bbb; border-radius: 8px; font-size: 15px; outline: none; transition: 0.2s; }
    .input-field:focus { border-color: #2980b9; box-shadow: 0 0 5px rgba(41,128,185,0.4); }
    .input-file { padding: 6px; }
    .submit-btn { background: steelblue; color: white; border: none; padding: 12px; font-size: 16px; border-radius: 8px; cursor: pointer; transition: background 0.25s ease; margin-top: 10px; }
    .submit-btn:hover { background: #1f5f8b; }
    #icon { font-family: "Font Awesome 5 Free", Arial, sans-serif; font-weight: 900; font-size: 16px; padding: 10px; }
    #icon option { font-family: "Font Awesome 5 Free", Arial, sans-serif; font-weight: 900; padding: 6px; }

    /* Dark Mode Styles */
    body.dark-mode .form-title { color: #66b3ff; }
    body.dark-mode .success { color: #6bff7b; }
    body.dark-mode .error { color: #ff6b6b; }
    body.dark-mode .form-container { background: #2a2a2a; box-shadow: 0 4px 18px rgba(0,0,0,0.3); }
    body.dark-mode .form-container label { color: #e0e0e0; }
    body.dark-mode .input-field { background: #1a1a1a; color: #e0e0e0; border-color: #333; }
    body.dark-mode .input-field:focus { border-color: #66b3ff; box-shadow: 0 0 5px rgba(102,179,255,0.3); }
    body.dark-mode .input-file { background: #1a1a1a; color: #e0e0e0; }
    body.dark-mode .submit-btn { background: #1e3fa0; }
    body.dark-mode .submit-btn:hover { background: #2555a8; }
    
    @media (max-width: 768px) {
        .search-container {
            display: none !important;
        }
    }
</style>
HTML;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fach hinzufügen - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?= $extraHead ?>
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Fach hinzufügen</span>

    <div class="login-status">
        <a href="javascript:history.back()" class="auth-btn">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
        <a href="../../logout.php" id="logout-btn" class="auth-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<?php include '../../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $conn = $database->getConnection();

    $name = $_POST['name'];
    $icon = $_POST['icon'];

    // Bild-Upload
    $bild = null;

    if (!empty($_FILES['bild_datei']['name'])) {
        $uploadDir = "../../img/";

        // Dateiname erstellen
        $filename = time() . "_" . basename($_FILES['bild_datei']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['bild_datei']['tmp_name'], $targetPath)) {
            $bild = "img/" . $filename;
        } else {
            die(" Fehler: Bild konnte nicht hochgeladen werden.");
        }
      }

    // INSERT in Datenbank
    $sql = "INSERT INTO hauptfaecher (name, bild, icon) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $bild, $icon]);

    $message = "<p class='success'>✔ Fach '{$name}' erfolgreich hinzugefügt!</p>";
    $conn = null;
}
?>

<main>
    <div class="content">

        <h2 class="form-title">Neues Fach hinzufügen</h2>

        <?= $message ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">

                <label for="fach_name">Fachname:</label>
                <input id="fach_name" type="text" name="name" class="input-field" required>

                <label for="bild_datei">Bild vom Desktop wählen:</label>
                <input id="bild_datei" type="file" name="bild_datei" class="input-file" accept="image/*">

                <label for="icon">Icon auswählen:</label>
                <select id="icon" name="icon" class="input-field">
                    <option value="" style="font-family: Arial, sans-serif;">-- BITTE ICON WÄHLEN --</option>
                    <?php
                    echo $schoolIconsOptions;
                    ?>
                </select>

                <button type="submit" class="submit-btn">Fach hinzufügen</button>
            </form>
        </div>

    </div>
</main>

<footer>
    <div id="imp"><a href="../../impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>

<?php include '../../includes/accessibility.php'; ?>
<script src="../../js/accessibility.js"></script>
<script src="../../js/header-responsive.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var iconSelect = document.getElementById('icon');
    if (!iconSelect) return;

    function updateSelectFont() {
        if (iconSelect.value === '') {
            iconSelect.style.fontFamily = 'Arial, sans-serif';
            iconSelect.classList.remove('fa-icons');
        } else {
            iconSelect.style.fontFamily = '"Font Awesome 5 Free", Arial, sans-serif';
            iconSelect.classList.add('fa-icons');
        }
    }

    updateSelectFont();
    iconSelect.addEventListener('change', updateSelectFont);
});
</script>
</body>
</html>
