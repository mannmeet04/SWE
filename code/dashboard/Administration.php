<?php
session_start();

// Session-Defaults
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

// Setze Seitentitel für Header
$pageTitle = $isTeacher ? 'Lehrerbereich' : 'Administration';

// Datenbankverbindung für Sidebar
require_once "config/database.php";
$database = new Database();
$conn = $database->getConnection();

$hauptfaecher = [];
if ($conn) {
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
}

// Globale Variable für die Sidebar
$GLOBALS['hauptfaecher'] = $hauptfaecher;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lernwebseite - <?= $pageTitle ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="img/logo.png" alt="Logo">
    </button>
    <span class="page-title"><?= $pageTitle ?></span>

    <div class="search-container">
        <form action="pages/search.php" method="GET" class="search-form">
            <input type="text" name="query" id="search-input" placeholder="Suchen..." autocomplete="off">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
        <div id="search-results-dropdown" class="dropdown-content"></div>
    </div>

    <div class="login-status">
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

<?php include 'includes/sidebar.php'; ?>

<main>
    <div class="content">
        <h1><?= $pageTitle ?></h1>

        <?php if (!$isAdmin && !$isTeacher): ?>
            <div class="error">Du hast keine Berechtigung, diese Seite zu sehen.</div>
        <?php else: ?>
            <p style="text-align: center;">
                <?= $isTeacher ? 'Willkommen im Lehrerbereich.' : 'Willkommen im Adminbereich.' ?>
                Wähle eine Aktion aus der Sidebar oder nutze die schnellen Links unten.
            </p>

            <div class="admin-links-container">
                <?php if ($isTeacher || $isAdmin): ?>
                    <div class="admin-link-card">
                        <a class="admin-link" href="pages/admin/admin_fach_hinzufuegen.php">
                            <div class="admin-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <h3>Fach hinzufügen</h3>
                            <p>Neue Fächer zum System hinzufügen</p>
                        </a>
                    </div>

                    <div class="admin-link-card">
                        <a class="admin-link" href="pages/admin/admin_fach_loeschen.php">
                            <div class="admin-icon">
                                <i class="fas fa-minus-circle"></i>
                            </div>
                            <h3>Fach löschen</h3>
                            <p>Vorhandene Fächer entfernen</p>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <div class="admin-link-card">
                        <a class="admin-link" href="pages/admin/accounts_uebersicht.php">
                            <div class="admin-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <h3>Personen verwalten</h3>
                            <p>Benutzerkonten bearbeiten</p>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<footer>
    <div id="imp"><a href="impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>
<?php include 'includes/accessibility.php'; ?>
<script src="js/accessibility.js"></script>
<script src="js/search-mobile.js"></script>
<script src="js/header-responsive.js"></script>

<style>
    .admin-links-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 40px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .admin-link-card {
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e1e8ed;
        text-align: center;
    }

    .admin-link-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border-color: var(--accent-color);
    }

    .admin-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .admin-icon {
        font-size: 3em;
        color: var(--accent-color);
        margin-bottom: 20px;
    }

    .admin-link-card h3 {
        color: var(--primary-color);
        margin-bottom: 10px;
        font-size: 1.5em;
    }

    .admin-link-card p {
        color: #666;
        font-size: 0.95em;
        line-height: 1.5;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #f5c6cb;
        text-align: center;
        font-weight: bold;
        margin: 20px 0;
    }

    @media (max-width: 768px) {
        .admin-links-container {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 0 15px;
        }

        .admin-link-card {
            padding: 20px;
        }
    }
</style>

</body>
</html>