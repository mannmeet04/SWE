<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Themenseite Deutsch</title>

    <link rel="stylesheet" href="../../dashboard/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Toggle-Bar Styling */
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

        /* Section Styling */
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

        /* Content Cards */
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

        /* Sidebar ohne JavaScript */
        .sidebar {
            position: fixed;
            left: -250px;
            top: 0;
            height: 100%;
            width: 250px;
            background: var(--primary-color);
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar:target {
            left: 0;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar:target + .overlay {
            display: block;
        }

        .close-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            color: white;
            font-size: 24px;
            text-decoration: none;
        }
    </style>
</head>

<body>
<?php
// Aktiven Tab aus GET-Parameter lesen oder Standard auf "erklaerung" setzen
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'erklaerung';
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="#" class="close-btn">&times;</a>
    <nav>
        <a href="../../dashboard/dashboard.php"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
        <a href="../../dashboard/dashboard.php"><i class="fa-solid fa-book"></i><span>Fächer</span></a>
        <a href="../../dashboard/dashboard.php"><i class="fa-solid fa-star"></i><span>Favoriten</span></a>
        <a href="../../dashboard/dashboard.php"><i class="fa-solid fa-gear"></i><span>Einstellungen</span></a>
    </nav>
</div>

<div class="overlay"></div>

<header>
    <a href="#sidebar" class="menu-btn">☰</a>
    <a href="../../dashboard/dashboard.php" class="logo-btn">
        <img src="../../dashboard/img/logo.png" alt="Logo">
    </a>
    <span>Deutsch – Wortarten</span>
</header>

<!-- Inhalt -->
<main class="content">

    <!-- Toggle-Bar -->
    <div class="toggle-bar">
        <a href="?tab=erklaerung" class="toggle-option <?php echo $active_tab == 'erklaerung' ? 'active' : ''; ?>">
            <i class="fa-solid fa-book-open"></i> Erklärung
        </a>
        <a href="?tab=uebungen" class="toggle-option <?php echo $active_tab == 'uebungen' ? 'active' : ''; ?>">
            <i class="fa-solid fa-pen-to-square"></i> Übungen
        </a>
        <a href="?tab=videos" class="toggle-option <?php echo $active_tab == 'videos' ? 'active' : ''; ?>">
            <i class="fa-solid fa-play"></i> Videos
        </a>
    </div>

    <!-- Erklärung -->
    <section id="erklaerung" class="toggle-section <?php echo $active_tab == 'erklaerung' ? 'active' : ''; ?>">
        <div class="content-card">
            <h2>Was sind Wortarten?</h2>
            <p>
                Wortarten sind Gruppen von Wörtern mit ähnlicher Funktion. Zum Beispiel:
            </p>
            <ul>
                <li><strong>Nomen:</strong> Dinge, Personen, Orte (<em>Haus, Lehrer, Berlin</em>)</li>
                <li><strong>Verben:</strong> Handlungen oder Zustände (<em>laufen, schlafen, sein</em>)</li>
                <li><strong>Adjektive:</strong> beschreiben Nomen (<em>schön, laut, freundlich</em>)</li>
                <li><strong>Artikel:</strong> Begleiter von Nomen (<em>der, die, das, ein, eine</em>)</li>
                <li><strong>Pronomen:</strong> Fürwörter (<em>ich, du, er, sie, es</em>)</li>
            </ul>
            <p>
                Diese Grundlagen helfen dir, Sätze richtig zu bilden und zu verstehen.
            </p>
        </div>
    </section>

    <!-- Übungen -->
    <section id="uebungen" class="toggle-section <?php echo $active_tab == 'uebungen' ? 'active' : ''; ?>">
        <div class="content-card">
            <h2>Übungsblätter</h2>
            <p>Lade dir passende Arbeitsblätter herunter:</p>
            <div class="uebung-grid">
                <a class="uebung-card" >
                    <i class="fa-solid fa-file-pdf" style="font-size: 2em; color: #e74c3c; margin-bottom: 10px;"></i>
                    <div><strong>Übung 1 – Grundlagen</strong><br><small>Einsteiger</small></div>
                </a>
                <a class="uebung-card" >
                    <i class="fa-solid fa-file-pdf" style="font-size: 2em; color: #e74c3c; margin-bottom: 10px;"></i>
                    <div><strong>Übung 2 – Alle Wortarten</strong><br><small>Fortgeschritten</small></div>
                </a>
                <a class="uebung-card">
                    <i class="fa-solid fa-file-pdf" style="font-size: 2em; color: #e74c3c; margin-bottom: 10px;"></i>
                    <div><strong>Übung 3 – Gemischt</strong><br><small>Alle Wortarten</small></div>
                </a>
            </div>
        </div>
    </section>

    <!-- Videos -->
    <section id="videos" class="toggle-section <?php echo $active_tab == 'videos' ? 'active' : ''; ?>">
        <div class="content-card">
            <h2>Erklärvideos</h2>
            <p>Schau dir die folgenden Videos zum Thema an:</p>

            <div style="margin: 25px 0;">
                <h3>Wortarten einfach erklärt</h3>
                <div style="position:relative; padding-top:56.25%; border-radius: 10px; overflow: hidden;">
                    <iframe src="https://www.youtube.com/embed/1i1JPN4ulaE"
                            title="Wortarten erklärt"
                            style="position:absolute; left:0; top:0; width:100%; height:100%; border:0;"
                            allowfullscreen></iframe>
                </div>
            </div>

        </div>
    </section>
</main>

</body>
</html>