<?php
session_start();
require_once "../../config/database.php";

// Nur für Admins zugänglich
if (!isset($_SESSION['angemeldet']) || $_SESSION['rolle'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Alle Benutzer laden
$sql = "SELECT * FROM benutzer ORDER BY rolle, benutzername";
$stmt = $conn->prepare($sql);
$stmt->execute();
$benutzer = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nachricht nach Löschung/Änderung
$message = $_GET['message'] ?? '';

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
    <title>Accountverwaltung - Lernwebseite</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Accountverwaltung</span>

    <div class="login-status">
        <a href="../../Administration.php" class="auth-btn">
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Accountverwaltung</h1>
        </div>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Alle Benutzer</h2>
                <a href="account_erstellen.php"
                   style="padding: 10px 15px; background: #27ae60; color: white; text-decoration: none; border-radius: 6px;">
                    <i class="fas fa-user-plus"></i> Neuen Account erstellen
                </a>
            </div>

            <table class="accounts-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Benutzername</th>
                    <th>E-Mail</th>
                    <th>Rolle</th>
                    <th>Erstellt am</th>
                    <th>Aktionen</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($benutzer as $user): ?>
                    <?php
                    $roleColors = [
                            'admin' => '#e74c3c',
                            'lehrer' => '#9b59b6',
                            'schüler' => '#3498db'
                    ];
                    $roleNames = [
                            'admin' => 'Administrator',
                            'lehrer' => 'Lehrer',
                            'schüler' => 'Schüler'
                    ];
                    ?>
                    <tr>
                        <td data-label="ID"><?= $user['id'] ?></td>
                        <td data-label="Benutzername"><?= htmlspecialchars($user['benutzername']) ?></td>
                        <td data-label="E-Mail"><?= htmlspecialchars($user['email']) ?></td>
                        <td data-label="Rolle">
                                    <span style="background: <?= $roleColors[$user['rolle']] ?? '#95a5a6' ?>;
                                            color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                                        <?= $roleNames[$user['rolle']] ?? $user['rolle'] ?>
                                    </span>
                        </td>
                        <td data-label="Erstellt am"><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                        <td data-label="Aktionen">
                            <div class="account-actions">
                                <a href="account_bearbeiten.php?id=<?= $user['id'] ?>"
                                   class="account-action-btn edit">
                                    <i class="fas fa-edit"></i> Bearbeiten
                                </a>
                                <a href="account_reset_password.php?id=<?= $user['id'] ?>"
                                   class="account-action-btn reset">
                                    <i class="fas fa-key"></i> Passwort
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="account_loeschen.php?id=<?= $user['id'] ?>"
                                       class="account-action-btn delete"
                                       onclick="return confirm('Account wirklich löschen?');">
                                        <i class="fas fa-trash"></i> Löschen
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
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