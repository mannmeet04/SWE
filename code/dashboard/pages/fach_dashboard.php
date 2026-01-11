
<?php
session_start();
require_once "../config/database.php";

// Bessere Session-Prüfung
$isLoggedIn = isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
$username = $_SESSION['benutzername'] ?? '';
$userRole = $_SESSION['rolle'] ?? 'guest';
$isAdmin = $userRole === 'admin';
$isTeacher = $userRole === 'lehrer';
$isStudent = $userRole === 'schüler';

$database = new Database();
$conn = $database->getConnection();


$favUnterthemenIds = [];

if ($isLoggedIn ) {
    $sql = "SELECT unterthema_id
            FROM favoriten_unterthemen
            WHERE benutzer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $favUnterthemenIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}





// Hauptfach-ID aus URL
$hauptfach_id = isset($_GET['hauptfach_id']) ? intval($_GET['hauptfach_id']) : 1;

// Aktuelles Hauptfach laden
$sql = "SELECT * FROM hauptfaecher WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$hauptfach_id]);
$aktuelles_fach = $stmt->fetch(PDO::FETCH_ASSOC);

// Unterthemen dieses Hauptfachs laden
$sql = "SELECT * FROM unterthemen WHERE hauptfach_id = ? ORDER BY sort_order";
$stmt = $conn->prepare($sql);
$stmt->execute([$hauptfach_id]);
$unterthemen = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hauptfächer für Sidebar MIT Unterthemen laden
$sql = "SELECT h.*, 
               (SELECT COUNT(*) FROM unterthemen WHERE hauptfach_id = h.id) as unterthemen_count
        FROM hauptfaecher h 
        ORDER BY h.sort_order";
$stmt = $conn->prepare($sql);
$stmt->execute();
$hauptfaecher = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Für jedes Hauptfach die Unterthemen laden
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
    <title><?= htmlspecialchars($aktuelles_fach['name']) ?> - Lernwebseite</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    <header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../img/logo.png" alt="Logo">
    </button>
    <span class="page-title"><?= htmlspecialchars($aktuelles_fach['name']) ?></span>

    <div class="search-container">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="query" id="search-input" placeholder="Suchen..." required autocomplete="off">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
        <div id="search-results-dropdown" class="dropdown-content"></div>
    </div>

        <div class="login-status">
            <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($username) ?></span>
                    <span class="user-role">
                    <?php
                    $roleBadge = [
                            'admin' => 'Administrator',
                            'lehrer' => 'Lehrer',
                            'schüler' => 'Schüler',
                            'guest' => 'Gast'
                    ];
                    echo $roleBadge[$userRole] ?? 'Gast';
                    ?>
                </span>
                </div>
                <a href="../logout.php" id="logout-btn" class="auth-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="../login.php" id="login-btn" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php include '../includes/sidebar.php'; ?>

    <main>
        <div class="content">
            <h1><?= htmlspecialchars($aktuelles_fach['name']) ?></h1>

            <h2>Unterthemen</h2>

            <?php if ($isTeacher || $isAdmin): ?>
                <!-- Buttons für Unterthemen -->
                <div class="button_mittig">
                    <!-- Unterthema hinzufügen -->
                    <a href="admin/unterthemen_hinzufuegen.php?hauptfach_id=<?= $hauptfach_id ?>"
                       style="padding:7px; background: #219653;
                        color: white; text-decoration:none; border-radius:7px; margin-right:10px;">
                        + Unterthema hinzufügen
                    </a>

                    <!-- Unterthema löschen -->
                    <a href="admin/unterthemen_loeschen.php?hauptfach_id=<?= $hauptfach_id ?>"
                       style="padding:7px; background-color: #e74c3c;
                        color: white; text-decoration:none; border-radius:7px;">
                        - Unterthema löschen
                    </a>
                </div>
            <?php endif; ?>

            <div class="fach-container">
                <?php foreach ($unterthemen as $unterthema): ?>
                    <?php $isFav = in_array($unterthema['id'], $favUnterthemenIds); ?>

                    <div class="fach">
                        <a href="unterthema.php?unterthema_id=<?= $unterthema['id'] ?>&tab=erklaerung">
                            <img src="../<?= htmlspecialchars($unterthema['bild']) ?>" alt="<?= htmlspecialchars($unterthema['name']) ?>">
                        </a>
                        <a class="fach2" href="unterthema.php?unterthema_id=<?= $unterthema['id'] ?>&tab=erklaerung">
                            <?= htmlspecialchars($unterthema['name']) ?>
                        </a>

                        <?php if ($isLoggedIn ): ?>
                            <?php $isFav = in_array($unterthema['id'], $favUnterthemenIds); ?>

                            <form action="../toggle_favorit.php" method="POST" class="fav-form">
                                <input type="hidden" name="unterthema_id" value="<?= $unterthema['id'] ?>">

                                <button
                                        type="submit"
                                        class="fav-btn <?= $isFav ? 'fav-active' : '' ?>"
                                        aria-label="Unterthema als Favorit markieren"
                                >
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    </main>

    <footer>
        <div id="imp"><a href="../impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
    </footer>

    <?php include '../includes/accessibility.php'; ?>
    <script src="../js/accessibility.js"></script>
    <script src="../js/search-mobile.js"></script>
    <script src="../js/header-responsive.js"></script>

</body>
</html>