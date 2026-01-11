<?php
session_start();//h
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


// Hauptfächer mit Unterthemen laden (Wird für die Sidebar benötigt)
$hauptfaecher = [];
$sql = "SELECT h.*, 
               (SELECT COUNT(*) FROM unterthemen WHERE hauptfach_id = h.id) as unterthemen_count
        FROM hauptfaecher h 
        ORDER BY h.sort_order";
$stmt = $conn->prepare($sql);
$stmt->execute();
$hauptfaecher = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Für jedes Hauptfach die Unterthemen laden (für die Sidebar-Struktur)
foreach($hauptfaecher as &$fach) {
    $sql = "SELECT * FROM unterthemen WHERE hauptfach_id = ? ORDER BY sort_order";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$fach['id']]);
    $fach['unterthemen'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($fach);
// ---------------------------------------------------------------------

// --- LOGIK ZUR SUCHE DER ERGEBNISSE ---
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];

if (!empty($search_query)) {
    // Wildcard-Zeichen für SQL-LIKE-Abfragen
    $search_param = '%' . $search_query . '%';

    // 1. Suche in Hauptfächern (Name)
    // KORREKTUR: || durch CONCAT() ersetzt
    $sql_faecher = "SELECT 
                        name, 
                        'Hauptfach' as typ, 
                        NULL as inhalt_auszug, 
                        CONCAT('fach_dashboard.php?hauptfach_id=', id) as url
                    FROM hauptfaecher
                    WHERE name LIKE ?";
    $stmt_faecher = $conn->prepare($sql_faecher);
    $stmt_faecher->execute([$search_param]);
    $results = array_merge($results, $stmt_faecher->fetchAll(PDO::FETCH_ASSOC));

    // 2. Suche in Unterthemen (Name)
    // KORREKTUR: || durch CONCAT() ersetzt
    $sql_unterthemen = "SELECT 
                            name, 
                            'Unterthema' as typ, 
                            NULL as inhalt_auszug, 
                            CONCAT('unterthema.php?unterthema_id=', id, '&tab=erklaerung') as url
                        FROM unterthemen
                        WHERE name LIKE ?";
    $stmt_unterthemen = $conn->prepare($sql_unterthemen);
    $stmt_unterthemen->execute([$search_param]);
    $results = array_merge($results, $stmt_unterthemen->fetchAll(PDO::FETCH_ASSOC));

    // 3. Suche in Lerninhalten (Titel und Inhalt)
    // KORREKTUR: || durch CONCAT() ersetzt
    $sql_inhalte = "SELECT 
                        l.titel as name, 
                        'Lerninhalt' as typ, 
                        SUBSTR(l.inhalt, 1, 150) as inhalt_auszug,
                        CONCAT('unterthema.php?unterthema_id=', l.unterthema_id, '&tab=', l.typ) as url
                    FROM lerninhalte l
                    JOIN unterthemen u ON l.unterthema_id = u.id
                    WHERE l.titel LIKE ? OR l.inhalt LIKE ?";
    $stmt_inhalte = $conn->prepare($sql_inhalte);
    $stmt_inhalte->execute([$search_param, $search_param]);
    $results = array_merge($results, $stmt_inhalte->fetchAll(PDO::FETCH_ASSOC));

    // Duplikate entfernen und finalisieren
    $unique_results = [];
    foreach ($results as $item) {
        $unique_results[$item['url']] = $item;
    }
    $results = array_values($unique_results);
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suche: <?= htmlspecialchars($search_query) ?> - Lernwebseite</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .search-result-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            /* Optional: Mauszeiger ändern, um Klickbarkeit zu signalisieren */
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .search-result-item:hover {
            background-color: #f7f7f7;
        }
        .search-result-item h3 {
            margin-top: 0;
            margin-bottom: 5px;
            color: #3498db;
        }
        .search-result-item h3 a {
            text-decoration: none;
            color: inherit;
            display: block; /* Stellt sicher, dass der gesamte <h3>-Bereich der Link ist */
        }
        .search-result-item .meta-info {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 10px;
        }
        .search-result-item .excerpt {
            color: #444;
            margin-top: 5px;
        }

        /* Dark Mode für Suchergebnisse */
        body.dark-mode .search-result-item {
            background: #2a2a2a;
            border: 1px solid #444;
        }
        body.dark-mode .search-result-item:hover {
            background-color: #333;
        }
        body.dark-mode .search-result-item h3 {
            color: #66b3ff;
        }
        body.dark-mode .search-result-item h3 a {
            color: #66b3ff;
        }
        body.dark-mode .search-result-item .meta-info {
            color: #999;
        }
        body.dark-mode .search-result-item .excerpt {
            color: #bbb;
        }

    </style>
</head>
<body>
    <header>
        <button class="menu-btn logo-btn" onclick="toggleSidebar()">
            <img src="../img/logo.png" alt="Logo">
        </button>
        <span class="page-title"><?= htmlspecialchars($aktuelles_thema['hauptfach_name'] ?? 'Suche') ?> - <?= htmlspecialchars($search_query) ?></span>
        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="query" id="search-input" placeholder="Suchen..." required autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            <div id="search-results-dropdown" class="dropdown-content">
            </div>
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
        <h1>Suchergebnisse für "<?= htmlspecialchars($search_query) ?>"</h1>
        <p>Hier finden Sie alle Treffer aus Fächern und Lerninhalten:</p>

        <?php if (empty($search_query)): ?>
            <p>Bitte geben Sie einen Suchbegriff ein.</p>
        <?php elseif (empty($results)): ?>
            <p>Es wurden keine Ergebnisse für "<?= htmlspecialchars($search_query) ?>" gefunden. 🧐</p>
        <?php else: ?>
            <h2><?= count($results) ?> Treffer gefunden</h2>
            <div class="search-results">
                <?php foreach ($results as $result): ?>
                    <div class="search-result-item">
                        <h3>
                            <a href="<?= htmlspecialchars($result['url']) ?>">
                                <?= htmlspecialchars($result['name']) ?>
                            </a>
                        </h3>
                        <div class="meta-info">
                            <?= htmlspecialchars($result['typ']) ?>
                        </div>
                        <?php if (!empty($result['inhalt_auszug'])): ?>
                            <p class="excerpt">
                                Auszug: <?= htmlspecialchars($result['inhalt_auszug']) ?>...
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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