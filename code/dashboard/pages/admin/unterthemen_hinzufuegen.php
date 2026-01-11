<?php
global $schoolIconsOptions;

session_start();

// Prüfe, ob der Benutzer angemeldet und ein Admin ist
if (!isset($_SESSION['angemeldet']) || !in_array($_SESSION['rolle'], ['lehrer', 'admin'])) {
    header("Location: ../../dashboard.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../includes/icons.php";

// Hauptfach-ID aus URL (erforderlich)
$hauptfach_id = isset($_GET['hauptfach_id']) ? intval($_GET['hauptfach_id']) : null;

if ($hauptfach_id === null) {
    header("Location: ../../dashboard.php");
    exit;
}

$pageTitle = 'Unterthema hinzufügen';
$extraHead = <<<HTML
<style>
    .form-title { text-align: center; font-size: 28px; margin-top: 30px; color: steelblue; }
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
    /* Erfolg / Fehler Meldungen */
    .success { text-align: center; color: green; font-weight: bold; }
    .error { text-align: center; color: red; font-weight: bold; }
    
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
    <title>Unterthema hinzufügen - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?= $extraHead ?>
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Unterthema hinzufügen</span>

    <div class="login-status">
        <a href="../fach_dashboard.php?id=<?= htmlspecialchars($hauptfach_id) ?>" class="auth-btn">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
        <a href="../../logout.php" id="logout-btn" class="auth-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<?php include '../../includes/sidebar.php';

// Nachricht für Erfolg/Fehler
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $conn = $database->getConnection();

    $name = trim($_POST['name'] ?? '');
    $icon = $_POST['icon'] ?? '';

    // Einfache Validierung
    if ($name === '') {
        $message = "<div class='message-box error'>❌ Bitte einen Namen für das Unterthema eingeben.</div>";
    } else {
        // Bild-Upload
        $bild = null;

        if (!empty($_FILES['bild_datei']['name'])) {
            // Prüfe Upload-Fehler zuerst
            $fileError = $_FILES['bild_datei']['error'];
            $fileSize = $_FILES['bild_datei']['size'] ?? 0;

            // Hilfsfunktion: Upload-Fehler beschreiben
            function upload_error_message($code) {
                $errors = [
                    UPLOAD_ERR_OK => 'Es gab keinen Fehler beim Upload.',
                    UPLOAD_ERR_INI_SIZE => 'Die hochgeladene Datei überschreitet die upload_max_filesize-Direktive auf dem Server.',
                    UPLOAD_ERR_FORM_SIZE => 'Die hochgeladene Datei überschreitet die in der Form erlaubte Größe.',
                    UPLOAD_ERR_PARTIAL => 'Die Datei wurde nur teilweise hochgeladen.',
                    UPLOAD_ERR_NO_FILE => 'Es wurde keine Datei hochgeladen.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Fehlender temporärer Ordner auf dem Server.',
                    UPLOAD_ERR_CANT_WRITE => 'Fehler beim Schreiben der Datei auf die Festplatte.',
                    UPLOAD_ERR_EXTENSION => 'Eine PHP-Erweiterung hat den Upload gestoppt.'
                ];
                return $errors[$code] ?? 'Unbekannter Upload-Fehler.';
            }

            if ($fileError !== UPLOAD_ERR_OK) {
                // spezielle Hinweise bei Größen-Fehlern
                if ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
                    $message = "<div class='message-box error'>❌ Datei zu groß. Bitte kleinere Datei hochladen (Server-Limit: " . ini_get('upload_max_filesize') . ").</div>";
                } else {
                    $message = "<div class='message-box error'>❌ Upload-Fehler: " . htmlspecialchars(upload_error_message($fileError)) . "</div>";
                }
            } else {
                // Zielordner berechnen und prüfen
                $uploadDir = realpath(__DIR__ . '/../../img') ?: (__DIR__ . '/../../img');

                if (!is_dir($uploadDir)) {
                    // Versuch, Ordner zu erstellen
                    if (!mkdir($uploadDir, 0755, true)) {
                        $message = "<div class='message-box error'>❌ Zielordner existiert nicht und konnte nicht erstellt werden: " . htmlspecialchars($uploadDir) . "</div>";
                    }
                }

                if ($message === "") {
                    if (!is_writable($uploadDir)) {
                        $message = "<div class='message-box error'>❌ Zielordner ist nicht beschreibbar: " . htmlspecialchars($uploadDir) . "</div>";
                    }
                }

                // Falls noch kein Fehler, verschiebe die Datei
                if ($message === "") {
                    // Sanitize Dateiname und führe Kollisionsschutz durch
                    $originalName = basename($_FILES['bild_datei']['name']);
                    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                    $targetPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

                    if (move_uploaded_file($_FILES['bild_datei']['tmp_name'], $targetPath)) {
                        // Web-relativer Pfad
                        $bild = 'img/' . $safeName;
                    } else {
                        $message = "<div class='message-box error'>❌ Fehler: Datei konnte nicht verschoben werden.</div>";
                    }
                }
            }
        }

        // Nur wenn kein vorheriger Fehler (z.B. Upload) vorliegt, in DB einfügen
        if ($message === "") {
            // Bestimme next sort_order für dieses Hauptfach
            $stmt = $conn->prepare("SELECT MAX(sort_order) as max_sort FROM unterthemen WHERE hauptfach_id = ?");
            $stmt->execute([$hauptfach_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_sort = ($row && $row['max_sort'] !== null) ? intval($row['max_sort']) + 1 : 1;

            // Unterthema speichern
            $sql = "INSERT INTO unterthemen (name, bild, icon, hauptfach_id, sort_order)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([$name, $bild, $icon, $hauptfach_id, $next_sort]);
            if ($success) {
                $unterthema_id = $conn->lastInsertId();

                // Automatische Lerninhalte anlegen
                $lerninhalte = [
                    ['titel' => 'Erklärung', 'typ' => 'erklaerung', 'sort_order' => 1],
                    ['titel' => 'Übungen', 'typ' => 'uebung', 'sort_order' => 2],
                    ['titel' => 'Videos', 'typ' => 'video', 'sort_order' => 3],
                ];

                foreach ($lerninhalte as $inhalt) {
                    $sql = "INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, sort_order)
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $unterthema_id,
                        $inhalt['titel'],
                        'Inhalt folgt...',
                        $inhalt['typ'],
                        $inhalt['sort_order']
                    ]);
                }

                $message = "<div class='message-box success'>✔ Unterthema erfolgreich hinzugefügt!</div>";
                // Optional: Formular leeren / Werte zurücksetzen
                $name = '';
            } else {
                $err = $stmt->errorInfo();
                $message = "<div class='message-box error'>❌ Fehler beim Hinzufügen des Unterthemas: " . htmlspecialchars($err[2] ?? '') . "</div>";
            }
        }

    }

    $conn = null;
}
?>

<main>
    <div class="content">
        <!-- FORMULAR -->
        <h2 class="form-title">Unterthema hinzufügen</h2>

        <?= $message ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">

                <label for="unterthema_name">Unterthema-Name:</label>
                <input id="unterthema_name" type="text" name="name" class="input-field" required>

                <label for="bild_datei">Bild vom Desktop wählen:</label>
                <input id="bild_datei" type="file" name="bild_datei" class="input-file" accept="image/*">


                <label for="icon">Icon auswählen:</label>
                <select id="icon" name="icon" class="input-field">
                    <option value="" style="font-family: Arial, sans-serif;">-- BITTE ICON WÄHLEN --</option>
                    <?php
                    echo $schoolIconsOptions;
                    ?>
                </select>



                <button type="submit" class="submit-btn">Unterthema hinzufügen</button>
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
