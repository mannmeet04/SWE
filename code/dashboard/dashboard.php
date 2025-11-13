<?php
global $faecher;
include "faecher.php";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Lernwebseite - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!--<a href="login.php" class="logo-placeholder">M</a>-->
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="/code/dashboard/img/logo.png" alt="Logo">
    </button>
    <span>Mein Dashboard</span>
    <button id="login">Login</button>
</header>

<div class="sidebar" id="sidebar">
    <nav>
        <a href="dashboard.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>

            <?php foreach ($faecher as $fach): ?>
                <?php if (isset($fach['subfaecher']) && count($fach['subfaecher']) > 0): ?>
                    <div class="sidebar-dropdown">
                        <a href="<?= $fach['link'] ?>" class="dropdown-toggle">
                            <i class="<?= $fach['icon'] ?>"></i>
                            <span><?= $fach['name'] ?> <i class="fas fa-chevron-down dropdown-icon"></i></span>
                        </a>
                        <div class="submenu">
                            <?php foreach ($fach['subfaecher'] as $subfach): ?>
                                <a href="<?= $subfach['link'] ?>">
                                    <i class="<?= $subfach['icon'] ?>"></i>
                                    <span><?= $subfach['name'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $fach['link'] ?>">
                        <i class="<?= $fach['icon'] ?>"></i>
                        <span><?= $fach['name'] ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
    </nav>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="content">
    <h1>Willkommen auf der Lernwebseite des HSG-Gymnasiums! </h1>
    <h2>Wähle ein Fach zum Anzeigen von Lerninhalten</h2>

    <div class="fach-container">
        <?php foreach ($faecher as $fach): ?>
            <div class="fach">
                <a href="<?= $fach['link'] ?>">
                    <img src="/code/dashboard/<?= $fach['bild'] ?>" alt="<?= $fach['name'] ?>">
                </a>
                <a class="fach2" href="<?= $fach['link'] ?>"><?= $fach['name'] ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        sidebar.classList.toggle("active");

        // Overlay-Steuerung für alle Geräte
        overlay.classList.toggle("active", sidebar.classList.contains("active"));

        if (!sidebar.classList.contains("active") && window.innerWidth > 768) {
            overlay.classList.remove("active");
        }
    }

    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                document.getElementById("sidebar").classList.remove("active");
                document.getElementById("overlay").classList.remove("active");
            }
        });
    });

    window.addEventListener('resize', () => {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");

        if (window.innerWidth > 768) {
            sidebar.classList.remove("hidden");
            overlay.classList.remove("active");
        } else {
            sidebar.classList.remove("active");
            sidebar.classList.add("hidden");
            overlay.classList.remove("active");
        }
    });

    window.addEventListener('load', () => {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");

        if (window.innerWidth > 768) {
            sidebar.classList.remove("active");
            sidebar.classList.remove("hidden");
            overlay.classList.remove("active");
        } else {
            sidebar.classList.add("hidden");
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        }
    });
</script>

<footer>
    <div id = "imp">Impressum</div>
    <div id = "bar">Barrierefreiheit</div>
</footer>
</html>