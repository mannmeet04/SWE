<?php
// dashboard.php - OHNE JavaScript
session_start();

if (!isset($_SESSION['angemeldet'])) {
    $_SESSION['angemeldet'] = false;
    $_SESSION['rolle'] = 'guest';
    $_SESSION['benutzername'] = 'Gast';
}

$isLoggedIn = $_SESSION['angemeldet'];
$username = $_SESSION['benutzername'] ?? '';
$userRole = $_SESSION['rolle'] ?? 'guest';
$isAdmin = $userRole === 'admin';
$isTeacher = $userRole === 'lehrer';
$isStudent = $userRole === 'schüler';

require_once "config/database.php";

$database = new Database();
$conn = $database->getConnection();

$favoritenIds = [];

if ($isLoggedIn ) {
    $sql = "SELECT hauptfach_id FROM favoriten WHERE benutzer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $favoritenIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
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
    <title>Lernwebseite - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>

    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Mein Dashboard</span>

    <div class="search-container">
        <form action="pages/search.php" method="GET" class="search-form">
            <input type="text" name="query" id="search-input" placeholder="Suchen..." autocomplete="off">
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
            <a href="logout.php" id="logout-btn" class="auth-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" id="login-btn" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        <?php endif; ?>
    </div>
</header>

<?php
$GLOBALS['hauptfaecher'] = $hauptfaecher;
include 'includes/sidebar.php';
?>

<main>
    <div class="content">
        <h1>Willkommen auf der Lernwebseite des HSG-Gymnasiums!</h1>

        <?php if (!$isLoggedIn): ?>
            <div class="guest-info">
                <p><i class="fas fa-info-circle"></i> Sie sind als Gast angemeldet.
                    <a href="login.php">Melden Sie sich an</a> für erweiterte Funktionen.</p>
                <div class="guest-badge">
                    <i class="fas fa-user"></i> Gast
                </div>
            </div>
        <?php endif; ?>

        <h2>Wähle ein Fach zum Anzeigen von Lerninhalten</h2>
        <div class="fach-container">
            <?php foreach ($hauptfaecher as $fach): ?>
                <?php $isFav = in_array($fach['id'], $favoritenIds); ?>

                <div class="fach">
                    <a href="pages/fach_dashboard.php?hauptfach_id=<?= $fach['id'] ?>">
                        <?php if (!empty($fach['bild']) && file_exists($fach['bild'])): ?>
                            <img src="<?= htmlspecialchars($fach['bild']) ?>" alt="<?= htmlspecialchars($fach['name']) ?>">
                        <?php else: ?>
                            <div class="fach-placeholder">
                                <i class="<?= htmlspecialchars($fach['icon'] ?? 'fas fa-book') ?>"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                    <a class="fach2" href="pages/fach_dashboard.php?hauptfach_id=<?= $fach['id'] ?>">
                        <?= htmlspecialchars($fach['name']) ?>
                    </a>

                    <?php if ($isLoggedIn ): ?>
                        <form action="toggle_favorit.php" method="POST" class="fav-form">
                            <input type="hidden" name="hauptfach_id" value="<?= $fach['id'] ?>">
                            <button type="submit" class="fav-btn <?= $isFav ? 'fav-active' : '' ?>">
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
    <div id="imp"><a href="impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>
<?php include 'includes/accessibility.php'; ?>
<script src="js/accessibility.js"></script>
<script src="js/search-mobile.js"></script>
<script src="js/header-responsive.js"></script>
</body>
</html>