<?php

include "unterthemen.php";
include "faecher.php";

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Lernwebseite</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>

            <div class="logo-placeholder">M</div>

        <button class="menu-btn logo-btn" onclick="toggleSidebar()">
            <img src="img/logo.png" alt="Logo">
        </button>
    </header>
    <div class="sidebar" id="sidebar">
        <nav>

            <?php foreach ($faecher as $fach): ?>
                <a href="<?= $fach['link'] ?>">
                    <i class="<?= $fach['icon'] ?>"></i>
                    <span><?= $fach['name'] ?></span> </a>
            <?php endforeach; ?>

        </nav>
    </div>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
    <div class="content">
        <h1>Willkommen auf der Lernwebseite des HSG-Gymnasiums! </h1>
        <h2>Wähle ein Fach zum Anzeigen von Lerninhalten</h2>
    </div>


    <div class="fach-container">
        <?php foreach ($ut_mathe as $ut): ?>
            <div class="fach">
                <a href="<?= $ut['link'] ?>">
                    <img src="<?= $ut['bild'] ?>" alt="<?= $ut['name'] ?>">
                </a>
                <a class="fach2" href="<?= $ut['link'] ?>"><?= $ut['name'] ?></a>
            </div>
        <?php endforeach; ?>
    </div>

</body>

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
</html>
