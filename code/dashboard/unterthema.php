<?php
require_once "config/database.php";

$database = new Database();
$conn = $database->getConnection();

// Unterthema-ID aus URL
$unterthema_id = isset($_GET['unterthema_id']) ? intval($_GET['unterthema_id']) : 1;

// Aktuelles Unterthema laden mit Hauptfach-Info
$sql = "SELECT u.*, h.name as hauptfach_name 
        FROM unterthemen u 
        JOIN hauptfaecher h ON u.hauptfach_id = h.id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$unterthema_id]);
$aktuelles_thema = $stmt->fetch(PDO::FETCH_ASSOC);

// Lerninhalte laden
$sql = "SELECT * FROM lerninhalte WHERE unterthema_id = ? ORDER BY typ, sort_order";
$stmt = $conn->prepare($sql);
$stmt->execute([$unterthema_id]);
$lerninhalte = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Aktiven Tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'erklaerung';

$conn = null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?> - Lernwebseite</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .toggle-bar {
            display: flex;
            justify-content: center;
            background: var(--secondary-color);
            border-radius: 25px;
            padding: 5px;
            margin: 20px auto 30px;
            max-width: 400px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .toggle-option {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            color: white;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .toggle-option:hover {
            background: rgba(255,255,255,0.1);
            text-decoration: none;
            color: white;
        }

        .toggle-option.active {
            background: var(--accent-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .toggle-section {
            display: none;
        }

        .toggle-section.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .content-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: left;
        }

        .uebung-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .uebung-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s ease;
        }

        .uebung-card:hover {
            transform: translateY(-5px);
            text-decoration: none;
        }
    </style>
</head>

<body>

<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="img/logo.png" alt="Logo">
    </button>
    <span><?= htmlspecialchars($aktuelles_thema['hauptfach_name'] ?? 'Unbekannt') ?> - <?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?></span>
    <button id="login">Login</button>
</header>

<div class="sidebar" id="sidebar">
    <nav>
        <a href="dashboard.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>

        <?php foreach ($hauptfaecher as $fach): ?>
            <?php if ($fach['unterthemen_count'] > 0): ?>
                <!-- Fach mit Dropdown -->
                <div class="sidebar-dropdown">
                    <a href="fach_dashboard.php?hauptfach_id=<?= $fach['id'] ?>" class="dropdown-item">
                        <i class="<?= htmlspecialchars($fach['icon']) ?>"></i>
                        <span><?= htmlspecialchars($fach['name']) ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <div class="submenu">
                        <?php foreach ($fach['unterthemen'] as $unterthema): ?>
                            <a href="unterthema.php?unterthema_id=<?= $unterthema['id'] ?>">
                                <i class="<?= htmlspecialchars($unterthema['icon']) ?>"></i>
                                <span><?= htmlspecialchars($unterthema['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Fach ohne Dropdown -->
                <a href="fach_dashboard.php?hauptfach_id=<?= $fach['id'] ?>">
                    <i class="<?= htmlspecialchars($fach['icon']) ?>"></i>
                    <span><?= htmlspecialchars($fach['name']) ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Platzhalter -->
        <div class="sidebar-placeholder">
            <a href="#">
                <i class="fas fa-plus"></i>
                <span>Lerninhalt hinzufügen</span>
            </a>
        </div>
    </nav>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<main class="content">
    <h1><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?></h1>

    <!-- Toggle-Bar -->
    <div class="toggle-bar">
        <a href="?unterthema_id=<?= $unterthema_id ?>&tab=erklaerung" class="toggle-option <?= $active_tab == 'erklaerung' ? 'active' : '' ?>">
            <i class="fa-solid fa-book-open"></i> Erklärung
        </a>
        <a href="?unterthema_id=<?= $unterthema_id ?>&tab=uebungen" class="toggle-option <?= $active_tab == 'uebungen' ? 'active' : '' ?>">
            <i class="fa-solid fa-pen-to-square"></i> Übungen
        </a>
        <a href="?unterthema_id=<?= $unterthema_id ?>&tab=videos" class="toggle-option <?= $active_tab == 'videos' ? 'active' : '' ?>">
            <i class="fa-solid fa-play"></i> Videos
        </a>
    </div>

    <!-- Erklärung -->
    <section id="erklaerung" class="toggle-section <?= $active_tab == 'erklaerung' ? 'active' : '' ?>">
        <?php if (count(array_filter($lerninhalte, function($inhalt) { return $inhalt['typ'] == 'erklaerung'; })) > 0): ?>
            <?php foreach ($lerninhalte as $inhalt): ?>
                <?php if ($inhalt['typ'] == 'erklaerung'): ?>
                    <div class="content-card">
                        <h2><?= htmlspecialchars($inhalt['titel']) ?></h2>
                        <p><?= nl2br(htmlspecialchars($inhalt['inhalt'])) ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="content-card">
                <h2>Noch keine Erklärungen vorhanden</h2>
                <p>Für dieses Thema sind noch keine Erklärungen verfügbar.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Übungen -->
    <section id="uebungen" class="toggle-section <?= $active_tab == 'uebungen' ? 'active' : '' ?>">
        <?php if (count(array_filter($lerninhalte, function($inhalt) { return $inhalt['typ'] == 'uebung'; })) > 0): ?>
            <?php foreach ($lerninhalte as $inhalt): ?>
                <?php if ($inhalt['typ'] == 'uebung'): ?>
                    <div class="content-card">
                        <h2><?= htmlspecialchars($inhalt['titel']) ?></h2>
                        <p><?= nl2br(htmlspecialchars($inhalt['inhalt'])) ?></p>
                        <div class="uebung-grid">
                            <a class="uebung-card" href="#">
                                <i class="fa-solid fa-file-pdf" style="font-size: 2em; color: #e74c3c; margin-bottom: 10px;"></i>
                                <div><strong>Übungsblatt</strong><br><small>PDF Download</small></div>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="content-card">
                <h2>Noch keine Übungen vorhanden</h2>
                <p>Für dieses Thema sind noch keine Übungen verfügbar.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Videos -->
    <section id="videos" class="toggle-section <?= $active_tab == 'videos' ? 'active' : '' ?>">
        <?php if (count(array_filter($lerninhalte, function($inhalt) { return $inhalt['typ'] == 'video'; })) > 0): ?>
            <?php foreach ($lerninhalte as $inhalt): ?>
                <?php if ($inhalt['typ'] == 'video'): ?>
                    <div class="content-card">
                        <h2><?= htmlspecialchars($inhalt['titel']) ?></h2>
                        <p><?= nl2br(htmlspecialchars($inhalt['inhalt'])) ?></p>
                        <?php if ($inhalt['video_url']): ?>
                            <div style="position:relative; padding-top:56.25%; border-radius: 10px; overflow: hidden; margin-top: 20px;">
                                <iframe src="<?= htmlspecialchars($inhalt['video_url']) ?>"
                                        title="<?= htmlspecialchars($inhalt['titel']) ?>"
                                        style="position:absolute; left:0; top:0; width:100%; height:100%; border:0;"
                                        allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="content-card">
                <h2>Noch keine Videos vorhanden</h2>
                <p>Für dieses Thema sind noch keine Videos verfügbar.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active", sidebar.classList.contains("active"));

        if (!sidebar.classList.contains("active") && window.innerWidth > 768) {
            overlay.classList.remove("active");
        }
    }

    // Dropdown Funktionalität für Sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdown = this.closest('.sidebar-dropdown');
                dropdown.classList.toggle('active');
            });
        });

        // Schließe Sidebar bei Klick auf Link (mobile)
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    document.getElementById("sidebar").classList.remove("active");
                    document.getElementById("overlay").classList.remove("active");
                }
            });
        });
    });
</script>

<footer>
    <div id = "imp">Impressum</div>
    <div id = "bar">Barrierefreiheit</div>
</footer>
</body>
</html>