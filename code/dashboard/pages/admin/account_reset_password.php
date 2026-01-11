<?php
session_start();
require_once "../../config/database.php";
include_once "../../includes/password_functions.php";

// Nur für Admins zugänglich
if (!isset($_SESSION['angemeldet']) || $_SESSION['rolle'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;
$database = new Database();
$conn = $database->getConnection();

// Benutzerdaten laden
$sql = "SELECT benutzername FROM benutzer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$benutzer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$benutzer) {
    header("Location: accounts_uebersicht.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $neues_passwort = $_POST['neues_passwort'];
    $passwort_wiederholen = $_POST['passwort_wiederholen'];

    if ($neues_passwort !== $passwort_wiederholen) {
        $error = "Die Passwörter stimmen nicht überein.";
    } elseif (strlen($neues_passwort) < 6) {
        $error = "Das Passwort muss mindestens 6 Zeichen lang sein.";
    } else {
        // Passwort hashen und speichern
        $passwort_hash = hashPassword($neues_passwort, PASSWORD_DEFAULT);

        $sql = "UPDATE benutzer SET passwort_hash = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$passwort_hash, $user_id])) {
            $success = "Passwort erfolgreich zurückgesetzt!";
        } else {
            $error = "Fehler beim Zurücksetzen des Passworts.";
        }
    }
}
$sql = "SELECT h.*, 
               (SELECT COUNT(*) FROM unterthemen WHERE hauptfach_id = h.id) as unterthemen_count
        FROM hauptfaecher h 
        ORDER BY h.sort_order";
$stmt = $conn->prepare($sql);
$stmt->execute();
$hauptfaecher = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($hauptfaecher as &$fach) {
    $sql = "SELECT * FROM unterthemen WHERE hauptfach_id = ? ORDER BY sort_order";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$fach['id']]);
    $fach['unterthemen'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($fach);

$conn = null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort zurücksetzen - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Passwort zurücksetzen</span>

    <div class="login-status">
        <a href="accounts_uebersicht.php" class="auth-btn">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
        <a href="../../logout.php" id="logout-btn" class="auth-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<?php include '../../includes/sidebar.php'; ?>

<main>
    <div class="content">
        <h1>Passwort zurücksetzen für <?= htmlspecialchars($benutzer['benutzername']) ?></h1>

        <div class="admin-panel">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="account-form">
                <div class="form-group">
                    <label for="neues_passwort">Neues Passwort:</label>
                    <input type="password" id="neues_passwort" name="neues_passwort" required>
                </div>

                <div class="form-group">
                    <label for="passwort_wiederholen">Passwort wiederholen:</label>
                    <input type="password" id="passwort_wiederholen" name="passwort_wiederholen" required>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="log-ht">
                        <i class="fas fa-key"></i> Passwort zurücksetzen
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <div id="imp"><a href="../../impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>

<?php include '../../includes/accessibility.php'; ?>
<script src="../../js/accessibility.js"></script>
</body>
</html>