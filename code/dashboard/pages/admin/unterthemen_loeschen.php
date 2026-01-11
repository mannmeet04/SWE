<?php
session_start();

// Prüfe, ob der Benutzer angemeldet und ein Admin ist
if (!isset($_SESSION['angemeldet']) || !in_array($_SESSION['rolle'], ['lehrer', 'admin'])) {
    header("Location: ../../dashboard.php");
    exit;
}

require_once "../../config/database.php";

$hauptfach_id = isset($_GET['hauptfach_id']) ? intval($_GET['hauptfach_id']) : null;
$message = "";

$pageTitle = 'Unterthema löschen';
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
    <title>Unterthema löschen - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?= $extraHead ?>
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Unterthema löschen</span>

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    $database = new Database();
    $conn = $database->getConnection();

    // Prüfen, ob Unterthema existiert
    $sql = "SELECT * FROM unterthemen WHERE name = ? AND hauptfach_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $hauptfach_id]);
    $unterthema = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($unterthema) {

        $unterthema_id = $unterthema['id'];

        // 1. Lerninhalte löschen
        $sql = "DELETE FROM lerninhalte WHERE unterthema_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$unterthema_id]);

        // 2. Unterthema löschen
        $sql = "DELETE FROM unterthemen WHERE id = ? AND hauptfach_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$unterthema_id, $hauptfach_id]);

        $message = "<p class='success'>✔ Unterthema '$name' wurde gelöscht!</p>";
    } else {
        $message = "<p class='error'>❌ Unterthema '$name' existiert nicht!</p>";
    }
}
?>

<main>
    <div class="content">


        <h2 class="form-title">Unterthema löschen</h2>

        <?= $message ?>

        <div class="form-container">
            <form method="POST">

                <label for="unterthema_name">Unterthema-Name eingeben:</label>
                <input id="unterthema_name" type="text" name="name" class="input-field" required>

                <button type="submit" class="submit-btn">
                    Unterthema löschen
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
