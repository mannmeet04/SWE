<?php
global $hauptfaecher;
if (!isset($hauptfaecher)) {
    require_once __DIR__ . "/../config/database.php";
    $database = new Database();
    $conn = $database->getConnection();

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

$isLoggedIn = isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
$userRole = $_SESSION['rolle'] ?? 'guest';
$isAdmin = $userRole === 'admin';
$isTeacher = $userRole === 'lehrer';
$isStudent = $userRole === 'schüler';

$current_path = $_SERVER['PHP_SELF'];
$base_path = '';

if (strpos($current_path, 'pages/admin/') !== false) {
    $base_path = '../../';
} elseif (strpos($current_path, 'pages/') !== false) {
    $base_path = '../';
}
?>
<div class="sidebar" id="sidebar">
    <nav>
        <a href="<?= $base_path ?>dashboard.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>

        <?php foreach ($hauptfaecher as $fach): ?>
            <?php if (!empty($fach['unterthemen'])): ?>
                <div class="sidebar-dropdown">
                    <a href="<?= $base_path ?>pages/fach_dashboard.php?hauptfach_id=<?= $fach['id'] ?>" class="dropdown-item">
                        <i class="<?= htmlspecialchars($fach['icon']) ?>"></i>
                        <span><?= htmlspecialchars($fach['name']) ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <div class="submenu">
                        <?php foreach ($fach['unterthemen'] as $unterthema): ?>
                            <a href="<?= $base_path ?>pages/unterthema.php?unterthema_id=<?= $unterthema['id'] ?>&tab=erklaerung">
                                <i class="<?= htmlspecialchars($unterthema['icon']) ?>"></i>
                                <span><?= htmlspecialchars($unterthema['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= $base_path ?>pages/fach_dashboard.php?hauptfach_id=<?= $fach['id'] ?>">
                    <i class="<?= htmlspecialchars($fach['icon']) ?>"></i>
                    <span><?= htmlspecialchars($fach['name']) ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>



        <?php if ($isLoggedIn ): ?>
            <a href="<?= $base_path ?>favoriten.php">
                <i class="fas fa-star"></i>
                <span>Favoriten</span>
            </a>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <div class="sidebar-dropdown">
                <a href="<?= $base_path ?>Administration.php" class="dropdown-item" style="color: #ff6b6b;"> <!-- Leicht rötlich hervorgehoben für Admin -->
                    <i class="fas fa-user-shield"></i>
                    <span>Administration</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <div class="submenu">
                    <a href="<?= $base_path ?>pages/admin/admin_fach_hinzufuegen.php">
                        <i class="fas fa-plus"></i>
                        <span>Fach hinzufügen</span>
                    </a>
                    <a href="<?= $base_path ?>pages/admin/admin_fach_loeschen.php">
                        <i class="fas fa-minus"></i>
                        <span>Fach löschen</span>
                    </a>
                    <a href="<?= $base_path ?>pages/admin/accounts_uebersicht.php">
                        <i class="fas fa-users-cog"></i>
                        <span>Personen verwalten</span>
                    </a>
                </div>
            </div>

        <?php elseif ($isTeacher): ?>
            <div class="sidebar-dropdown">
                <a href="<?= $base_path ?>Administration.php" class="dropdown-item" style="color: #4ecdc4;"> <!-- Farblich für Lehrer -->
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Lehrerbereich</span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </a>
                <div class="submenu">
                    <a href="<?= $base_path ?>pages/admin/admin_fach_hinzufuegen.php">
                        <i class="fas fa-plus"></i>
                        <span>Fach hinzufügen</span>
                    </a>
                    <a href="<?= $base_path ?>pages/admin/admin_fach_loeschen.php">
                        <i class="fas fa-minus"></i>
                        <span>Fach löschen</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </nav>
</div>

<div class="overlay" id="overlay"></div>

<script>
    window.toggleSidebar = function() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");

        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    function isTouchDevice() {
        return (('ontouchstart' in window) ||
                (navigator.maxTouchPoints > 0) ||
                (navigator.msMaxTouchPoints > 0));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isMobile = window.innerWidth <= 768;
        const isTouch = isTouchDevice();
        if (isMobile) {
            const sidebar = document.getElementById("sidebar");
            if (sidebar) {
                sidebar.classList.remove('active');
            }
        }

        if (isTouch) {
            let style = document.createElement('style');
            style.textContent = `
                @media (hover: none) {
                    .sidebar a:hover,
                    .fach:hover,
                    .sidebar-dropdown:hover .submenu,
                    button:hover {
                        background-color: inherit !important;
                        transform: none !important;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        const dropdownItems = document.querySelectorAll('.sidebar-dropdown');

        dropdownItems.forEach(dropdown => {
            const item = dropdown.querySelector('.dropdown-item');
            const icon = item.querySelector('.dropdown-icon');

            if (icon) {
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                });
            }

            if (isTouchDevice()) {
                item.addEventListener('click', function(e) {
                    if (e.target.closest('.dropdown-icon')) {
                        return;
                    }

                    if (this.getAttribute('href') === '#') {
                        e.preventDefault();
                        e.stopPropagation();
                        dropdown.classList.toggle('active');
                    }
                });
            }
        });

        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', (e) => {

                if (e.target.closest('.dropdown-icon')) {
                    return;
                }

                if (window.innerWidth <= 768) {
                    document.getElementById("sidebar").classList.remove("active");
                    document.getElementById("overlay").classList.remove("active");
                }
            });
        });

        const overlay = document.getElementById("overlay");
        if(overlay) {
            overlay.addEventListener('click', function() {
                window.toggleSidebar();
            });
        }
    });
</script>