<?php
$faecher = [
        [
                "name" => "Mathe",
                "bild" => "img/mathe.jpg",
                "link" => "../faecher_dashboards/mathe_dashboard.html"
        ],
        [
                "name" => "Deutsch",
                "bild" => "img/deutsch.jpg",
                "link" => "../faecher_dashboards/deutsch_dashboard.html"
        ],
        [
                "name" => "Englisch",
                "bild" => "img/englisch.jpg",
                "link" => "../faecher_dashboards/englisch_dashboard.html"
        ],
        [
                "name" => "Geschichte",
                "bild" => "img/geschichte.jpg",
                "link" => "../faecher_dashboards/geschichte_dashboard.html"
        ],
        [
                "name" => "Biologie",
                "bild" => "img/biologie.jpg",
                "link" => "../faecher_dashboards/biologie_dashboard.html"
        ],
        [
                "name" => "Musik",
                "bild" => "img/musik.jpg",
                "link" => "../faecher_dashboards/musik_dashboard.html"
        ]
];

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Lernwebseite</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <span>Mein Dashboard</span>
    </header>
    <div class="sidebar" id="sidebar">
        <?php foreach ($faecher as $fach): ?>
            <a href="<?= $fach['link'] ?>"><?= $fach['name'] ?></a>
        <?php endforeach; ?>
    </div>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
    <div class="content">
        <h1>Willkommen auf der Lernwebseite des HSG-Gymnasiums! </h1>
        <p>Klicke oben links auf ☰, um die Sidebar zu öffnen.</p>
    </div>

    <div class="fach-container">
        <?php foreach ($faecher as $fach): ?>
            <div class="fach">
                <a href="<?= $fach['link'] ?>">
                    <img src="<?= $fach['bild'] ?>" alt="<?= $fach['name'] ?>">
                </a>
                <p><?= $fach['name'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</body>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    }

    function closeSidebar() {
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById("sidebar").classList.remove("active");
                document.getElementById("overlay").classList.remove("active");
            });
        });
    }



</script>

</html>
