<?php
require_once "config/database.php";

$database = new Database();
$conn = $database->getConnection();

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
    <title><?= htmlspecialchars($aktuelles_fach['name']) ?> - Lernwebseite</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="img/logo.png" alt="Logo">
    </button>
    <span><?= htmlspecialchars($aktuelles_fach['name']) ?></span>
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

<div class="content">
    <h1><?= htmlspecialchars($aktuelles_fach['name']) ?></h1>

    <h2>Unterthemen</h2>

    <div class="fach-container">
        <?php foreach ($unterthemen as $unterthema): ?>
            <div class="fach">
                <a href="unterthema.php?unterthema_id=<?= $unterthema['id'] ?>">
                    <img src="<?= htmlspecialchars($unterthema['bild']) ?>" alt="<?= htmlspecialchars($unterthema['name']) ?>">
                </a>
                <a class="fach2" href="unterthema.php?unterthema_id=<?= $unterthema['id'] ?>">
                    <?= htmlspecialchars($unterthema['name']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active", sidebar.classList.contains("active"));
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

</body>

<footer>
    <div id = "imp">Impressum</div>
    <div id = "bar">Barrierefreiheit</div>
</footer>
</html>