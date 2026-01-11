<?php
session_start();
require_once "../../config/database.php";
include_once "../../includes/password_functions.php";

// Nur für Admins zugänglich
if (!isset($_SESSION['angemeldet']) || $_SESSION['rolle'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benutzername = trim($_POST['benutzername']);
    $email = trim($_POST['email']);
    $passwort = $_POST['passwort'];
    $rolle = $_POST['rolle'];

    // Validierung
    if (empty($benutzername) || empty($email) || empty($passwort)) {
        $error = "Bitte alle Felder ausfüllen.";
    } else {
        // Prüfe ob E-Mail bereits existiert
        $sql = "SELECT id FROM benutzer WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Diese E-Mail-Adresse ist bereits registriert.";
        } else {
            // Passwort hashen
            $passwort_hash = hashPassword($passwort, PASSWORD_DEFAULT);

            // Benutzer erstellen
            $sql = "INSERT INTO benutzer (benutzername, email, passwort_hash, rolle) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt->execute([$benutzername, $email, $passwort_hash, $rolle])) {
                $success = "Account erfolgreich erstellt!";
                // Felder leeren
                $benutzername = $email = '';
            } else {
                $error = "Fehler beim Erstellen des Accounts.";
            }
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
    <title>Account erstellen - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Neuen Account erstellen</span>

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
        <h1>Neuen Account erstellen</h1>

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
                           value="<?= htmlspecialchars($benutzername ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">E-Mail:</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="passwort">Passwort:</label>
                    <input type="password" id="passwort" name="passwort" required>
                </div>

                <div class="form-group">
                    <label for="rolle">Rolle:</label>
                    <select id="rolle" name="rolle" class="role-selector" required>
                        <option value="schüler">Schüler</option>
                        <option value="lehrer">Lehrer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="log-ht">
                        <i class="fas fa-user-plus"></i> Account erstellen
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