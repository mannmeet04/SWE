<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['angemeldet'])) {
    header("Location: dashboard.php");
    exit;
}


$database = new Database();
$conn = $database->getConnection();
$userId = $_SESSION['user_id'];

// Favorisierte Hauptfächer
$stmt = $conn->prepare("
    SELECT h.*
    FROM favoriten f
    JOIN hauptfaecher h ON h.id = f.hauptfach_id
    WHERE f.benutzer_id = ?
    ORDER BY h.sort_order
");
$stmt->execute([$userId]);
$favoriten = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Favorisierte Unterthemen
$stmt = $conn->prepare("
    SELECT u.*
    FROM favoriten_unterthemen fu
    JOIN unterthemen u ON u.id = fu.unterthema_id
    WHERE fu.benutzer_id = ?
    ORDER BY u.sort_order
");
$stmt->execute([$userId]);
$favUnterthemen = $stmt->fetchAll(PDO::FETCH_ASSOC);
$favoritenHauptfachIds = [];
$favoritenUnterthemaIds = [];

foreach ($favoriten as $fav) {
    $favoritenHauptfachIds[] = $fav['id'];
}

foreach ($favUnterthemen as $ut) {
    $favoritenUnterthemaIds[] = $ut['id'];
}
// Alle Hauptfächer laden für Sidebar
$stmt = $conn->prepare("SELECT * FROM hauptfaecher ORDER BY sort_order");
$stmt->execute();
$hauptfaecher = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Unterthemen für jedes Hauptfach laden
foreach($hauptfaecher as &$fach) {
    $sql = "SELECT * FROM unterthemen WHERE hauptfach_id = ? ORDER BY sort_order";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$fach['id']]);
    $fach['unterthemen'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($fach);

$GLOBALS['hauptfaecher'] = $hauptfaecher;

$conn = null;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Favoriten</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Meine Favoriten</span>

    <div class="login-status">
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['benutzername']) ?></span>
            <span class="user-role">
                <?php
                $roleBadge = [
                    'admin' => 'Administrator',
                    'lehrer' => 'Lehrer',
                    'schüler' => 'Schüler',
                    'guest' => 'Gast'
                ];
                echo $roleBadge[$_SESSION['rolle'] ?? 'schüler'];
                ?>
            </span>
        </div>
        <a href="logout.php" id="logout-btn" class="auth-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<?php
// Sidebar braucht hauptfaecher - bereits am Anfang geladen
$GLOBALS['hauptfaecher'] = $hauptfaecher;
include 'includes/sidebar.php';
?>

<main>
    <div class="content">
        <h2>⭐ Meine Favoriten</h2>

        <?php if (!empty($favoriten)): ?>
            <h3>📘 Favorisierte Fächer</h3>

            <div class="fach-container">
                <?php foreach ($favoriten as $fav): ?>
                    <div class="fach">

                        <a href="pages/fach_dashboard.php?hauptfach_id=<?= $fav['id'] ?>">
                            <?php if (!empty($fav['bild']) && file_exists($fav['bild'])): ?>
                                <img src="<?= htmlspecialchars($fav['bild']) ?>" alt="<?= htmlspecialchars($fav['name']) ?>">
                            <?php else: ?>
                                <div class="fach-placeholder">
                                    <i class="<?= htmlspecialchars($fav['icon'] ?? 'fas fa-book') ?>"></i>
                                </div>
                            <?php endif; ?>
                        </a>

                        <a class="fach2" href="pages/fach_dashboard.php?hauptfach_id=<?= $fav['id'] ?>">
                            <?= htmlspecialchars($fav['name']) ?>
                        </a>

                        <form action="toggle_favorit.php" method="POST" class="fav-form">
                            <input type="hidden" name="hauptfach_id" value="<?= $fav['id'] ?>">
                            <button type="submit" class="fav-btn fav-active" aria-label="Fach aus Favoriten entfernen">
                                <i class="fas fa-star"></i>
                            </button>
                        </form>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ================= UNTERTHEMEN ================= -->
        <?php if (!empty($favUnterthemen)): ?>
            <h3>📗 Favorisierte Unterthemen</h3>

            <div class="fach-container">
                <?php foreach ($favUnterthemen as $ut): ?>
                    <div class="fach">

                        <a href="pages/unterthema.php?unterthema_id=<?= $ut['id'] ?>&tab=erklaerung">
                            <?php if (!empty($ut['bild'])): ?>
                                <img src="<?= htmlspecialchars($ut['bild']) ?>" alt="<?= htmlspecialchars($ut['name']) ?>">
                            <?php else: ?>
                                <div class="fach-placeholder">
                                    <i class="<?= htmlspecialchars($ut['icon'] ?? 'fas fa-book') ?>"></i>
                                </div>
                            <?php endif; ?>
                        </a>

                        <a class="fach2" href="pages/unterthema.php?unterthema_id=<?= $ut['id'] ?>&tab=erklaerung">
                            <?= htmlspecialchars($ut['name']) ?>
                        </a>

                        <form action="toggle_favorit.php" method="POST" class="fav-form">
                            <input type="hidden" name="unterthema_id" value="<?= $ut['id'] ?>">
                            <button type="submit" class="fav-btn fav-active" aria-label="Unterthema aus Favoriten entfernen">
                                <i class="fas fa-star"></i>
                            </button>
                        </form>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ================= GAR KEINE FAVORITEN ================= -->
        <?php if (empty($favoriten) && empty($favUnterthemen)): ?>
            <p style="text-align:center;">Du hast noch keine Favoriten.</p>
        <?php endif; ?>

    </div>
</main>


<footer>
    <div id="imp"><a href="impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>

<?php include 'includes/accessibility.php'; ?>
<script src="js/accessibility.js"></script>
<script src="js/header-responsive.js"></script>
</body>
</html>
