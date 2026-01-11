<?php
session_start();
require_once "../../config/database.php";

include_once"../../includes/password_functions.php";

// Nur für Admins zugänglich
if (!isset($_SESSION['angemeldet']) || $_SESSION['rolle'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;
$database = new Database();
$conn = $database->getConnection();

// Benutzerdaten laden
$sql = "SELECT * FROM benutzer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$benutzer = $stmt->fetch(PDO::FETCH_ASSOC);

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


if (!$benutzer) {
    header("Location: accounts_uebersicht.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = trim($_POST['benutzername']);
    $email = trim($_POST['email']);
    $rolle = $_POST['rolle'];

    // Prüfe ob E-Mail bereits von anderem Benutzer verwendet wird
    $sql = "SELECT id FROM benutzer WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email, $user_id]);

    if ($stmt->rowCount() > 0) {
        $error = "Diese E-Mail-Adresse wird bereits verwendet.";
    } else {
        // Update durchführen
        $sql = "UPDATE benutzer SET benutzername = ?, email = ?, rolle = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$benutzername, $email, $rolle, $user_id])) {
            $success = "Änderungen erfolgreich gespeichert!";
            // Aktualisierte Daten laden
            $benutzer['benutzername'] = $benutzername;
            $benutzer['email'] = $email;
            $benutzer['rolle'] = $rolle;
        } else {
            $error = "Fehler beim Speichern der Änderungen.";
        }
    }
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account bearbeiten - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Account bearbeiten</span>

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
        <h1>Account bearbeiten</h1>

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
                    <label for="benutzername">Benutzername:</label>
                    <input type="text" id="benutzername" name="benutzername"
                           value="<?= htmlspecialchars($benutzer['benutzername']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">E-Mail:</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($benutzer['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="rolle">Rolle:</label>
                    <select id="rolle" name="rolle" class="role-selector" required>
                        <option value="schüler" <?= $benutzer['rolle'] == 'schüler' ? 'selected' : '' ?>>Schüler</option>
                        <option value="lehrer" <?= $benutzer['rolle'] == 'lehrer' ? 'selected' : '' ?>>Lehrer</option>
                        <option value="admin" <?= $benutzer['rolle'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="log-ht">
                        <i class="fas fa-save"></i> Änderungen speichern
                    </button>
                </div>
            </form>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <h3>Passwort zurücksetzen</h3>
                <p>Um das Passwort zurückzusetzen, besuchen Sie die <a href="account_reset_password.php?id=<?= $user_id ?>">Passwort-Reset-Seite</a>.</p>
            </div>
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