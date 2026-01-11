<?php
session_start();

// Prüfe, ob der Benutzer angemeldet und ein Admin ist
if (!isset($_SESSION['angemeldet']) || !in_array($_SESSION['rolle'], ['lehrer', 'admin'])) {
    header("Location: ../../dashboard.php");
    exit;
}

require_once "../../config/database.php";

$message = "";

$pageTitle = 'Fach löschen';
$extraHead = <<<HTML
<style>
    .form-title { text-align: center; font-size: 28px; margin-top: 30px; color: #333; }
    .success { text-align: center; color: green; font-weight: bold; }
    .error { text-align: center; color: red; font-weight: bold; }
    .form-container { max-width: 450px; margin: 30px auto; padding: 25px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,0.12); }
    .form-container form { display: flex; flex-direction: column; gap: 18px; }
    .form-container label { font-size: 16px; font-weight: bold; color: #444; }
    .input-field { padding: 12px; border: 1px solid #bbb; border-radius: 8px; font-size: 15px; outline: none; transition: 0.2s; }
    .input-field:focus { border-color: #c0392b; box-shadow: 0 0 5px rgba(192,57,43,0.4); }
    .submit-btn { background: #c0392b; color: white; border: none; padding: 12px; font-size: 16px; border-radius: 8px; cursor: pointer; transition: background 0.25s ease; }
    .submit-btn:hover { background: steelblue; }

    /* Dark Mode Styles */
    body.dark-mode .form-title { color: #66b3ff; }
    body.dark-mode .success { color: #6bff7b; }
    body.dark-mode .error { color: #ff6b6b; }
    body.dark-mode .form-container { background: #2a2a2a; box-shadow: 0 4px 18px rgba(0,0,0,0.3); }
    body.dark-mode .form-container label { color: #e0e0e0; }
    body.dark-mode .input-field { background: #1a1a1a; color: #e0e0e0; border-color: #333; }
    body.dark-mode .input-field:focus { border-color: #66b3ff; box-shadow: 0 0 5px rgba(102,179,255,0.3); }
    body.dark-mode .submit-btn { background: #c0392b; }
    body.dark-mode .submit-btn:hover { background: #1e3fa0; }
    
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
    <title>Fach löschen - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?= $extraHead ?>
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Fach löschen</span>

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

$database = new Database();
$conn = $database->getConnection();

// Alle Fächer für Dropdown holen
$sql = "SELECT id, name FROM hauptfaecher";
$stmt = $conn->prepare($sql);
$stmt->execute();
$faecher = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fach_id = $_POST['fach_id'] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM hauptfaecher WHERE id = ?");
    $stmt->execute([$fach_id]);
    $fach = $stmt->fetch();

    if ($fach) {

        $stmt = $conn->prepare("SELECT id FROM unterthemen WHERE hauptfach_id = ?");
        $stmt->execute([$fach_id]);
        $unterthemen_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!empty($unterthemen_ids)) {
            $placeholders = implode(',', array_fill(0, count($unterthemen_ids), '?'));

            $sql = "DELETE FROM lerninhalte WHERE unterthema_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($unterthemen_ids);

            $stmt = $conn->prepare("DELETE FROM unterthemen WHERE hauptfach_id = ?");
            $stmt->execute([$fach_id]);
        }

        $stmt = $conn->prepare("DELETE FROM hauptfaecher WHERE id = ?");
        $stmt->execute([$fach_id]);

        $message = "<p class='success'>✔ Fach '{$fach['name']}' und seine Unterthemen wurden gelöscht!</p>";
    } else {
        $message = "<p class='error'>❌ Fach existiert nicht!</p>";
    }
}

$conn = null;
?>

<main>
    <div class="content">

        <h2 class="form-title">Fach löschen</h2>

        <?= $message ?>

        <div class="form-container">
            <form method="POST">

                <label for="fach_id">Fach auswählen:</label>
                <select id="fach_id" name="fach_id" class="input-field" required>
                    <option value="">-- Bitte wählen --</option>
                    <?php foreach ($faecher as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="submit-btn">
                    Fach löschen
                </button>
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
</body>
</html>
