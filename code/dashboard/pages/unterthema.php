<?php
session_start();//
require_once "../config/database.php";

//Session-Prüfung
$isLoggedIn = isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
$username = $_SESSION['benutzername'] ?? '';
$isAdmin = $isLoggedIn && isset($_SESSION['rolle']) && $_SESSION['rolle'] === 'admin';

$database = new Database();
$conn = $database->getConnection();

$data_src_url = 'pages/admin/';
$unterthema_id = 1;
$active_tab = 'erklaerung';

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

// Für jeden Lerninhalt die verknüpften Inhalte laden (bidirektional)
foreach($lerninhalte as &$inhalt) {
    $verknuepfungen_array = [];

    // 1. Direkte Verknüpfungen: Was ist mit diesem Inhalt verknüpft?
    $sql = "SELECT l.* FROM lerninhalte l 
            JOIN lerninhalt_verknuepfungen v ON l.id = v.verknuepfter_lerninhalt_id 
            WHERE v.lerninhalt_id = ?
            ORDER BY l.typ, l.sort_order";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$inhalt['id']]);
    $direkteVerknuepfungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($direkteVerknuepfungen as $v) {
        $verknuepfungen_array[$v['id']] = $v;
    }

    // 2. Inverse Verknüpfungen: Andere Inhalte, die diesen verknüpft haben
    $sql = "SELECT l.* FROM lerninhalte l 
            JOIN lerninhalt_verknuepfungen v ON l.id = v.lerninhalt_id 
            WHERE v.verknuepfter_lerninhalt_id = ?
            ORDER BY l.typ, l.sort_order";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$inhalt['id']]);
    $inverseVerknuepfungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($inverseVerknuepfungen as $v) {
        $verknuepfungen_array[$v['id']] = $v;
    }

    $inhalt['verknuepfungen'] = array_values($verknuepfungen_array);
}
unset($inhalt);

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
$active_tab = $_GET['tab'] ?? 'erklaerung';

$conn = null;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?> - Lernwebseite</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>

        .fa-file-pdf{
            color: #e74c3c;
        }
        .fa-file-image{
            color: #3555d5

        }
        .content-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5625em;
            margin-bottom: 1.25em;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .uebung-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25em;
            margin-top: 1.25em;
        }

        .uebung-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.25em;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s ease;
        }

        .uebung-card:hover {
            transform: translateY(-5px);
            text-decoration: none;
        }

        .button_mittig {
            justify-self: center;
        }

        .bearbeiten{
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
        }

        .link{
            text-decoration: none;
            color: inherit;
        }
        /*----------------------------------MathJax E-Test---------------------------------- */
        .exercise-container {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-family: sans-serif;
        }

        /* Stil für Inputs INNERHALB der Formel (Blanks) */
        .math-input-inline {
            width: 60px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            padding: 2px;
            margin: 0 4px;
            vertical-align: middle;
        }

        /* Stil für Inputs UNTER der Formel (Variablen) */
        .variable-input-row {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Feedback Farben */
        .correct {
            border-color: #2ecc71 !important;
            background-color: #eafaf1 !important;
        }
        .wrong {
            border-color: #e74c3c !important;
            background-color: #fadbd8 !important;
        }

        /* Buttons */
        .btn-group {
            margin-top: 15px;
        }
        button {
            cursor: pointer;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            margin-right: 10px;
            font-weight: bold;
        }
        .btn-check { background-color: #3498db; color: white; }
        .btn-solve { background-color: #95a5a6; color: white; }
        /*----------------------------------MathJax E-Test---------------------------------- */


        .verknuepfungen-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75em;
        }

        .verknuepfung-badge {
            background: #27ae60;
            color: white;
            padding: 0.5em 1em;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5em;
        }

        .verknuepfung-badge:hover {
            background: #229954;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
        }

        .verknuepfung-badge i {
            font-size: 0.9em;
        }
    </style>
    <!-- MathJax  -->
    <script>
        window.MathJax = {
            loader: { load: ['[tex]/html'] }, // Lädt das HTML-Erweiterungspaket
            tex: {
                packages: { '[+]': ['html'] }, // Aktiviert das Paket
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$','$$'], ['\\[','\\]']]
            },
            svg: { fontCache: 'global' }
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <!-- MathJax  -->

    <script>
        function switchTab(tabName) {
            // Alle Tabs verstecken
            const sections = document.querySelectorAll('.toggle-section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            // Gewünschten Tab anzeigen
            const targetSection = document.getElementById(tabName);
            if (targetSection) {
                targetSection.classList.add('active');
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    </script>

</head>

<body>

    <header>
        <button class="menu-btn logo-btn" onclick="toggleSidebar()">
            <img src="../img/logo.png" alt="Logo">
        </button>
        <span class="page-title"><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unterthema') ?></span>

        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
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
                        echo $roleBadge[$_SESSION['rolle']] ?? 'Gast';
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

    <main class="content">
        <a class="link" href="fach_dashboard.php?hauptfach_id=<?= $aktuelles_thema['hauptfach_id'] ?>"> <h1><?= htmlspecialchars($aktuelles_thema['hauptfach_name']) ?></h1> </a>
        <h2><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?></h2>
        <?php if (isset($_SESSION['angemeldet']) && ($_SESSION['rolle'] === 'admin' || $_SESSION['rolle'] === 'lehrer' )): ?>
            <div class="button_mittig">
                <a href="admin/unterthema_bearbeiten.php?unterthema_id=<?= $aktuelles_thema['id'] ?>&tab=<?= $active_tab ?>" class="bearbeiten">
                    <i class="fa-solid fa-pen"></i> Unterthema bearbeiten
                </a>
            </div>
        <?php endif; ?>
        <!-- Toggle-Bar -->
        <?php
        include '../includes/toggle_bar.php';
        ?>
        <!-- Erklärung -->




        <section id="erklaerung" class="toggle-section <?= $active_tab == 'erklaerung' ? 'active' : '' ?>">
            <?php if (count(array_filter($lerninhalte, function($inhalt) { return $inhalt['typ'] == 'erklaerung'; })) > 0): ?>
                <?php foreach ($lerninhalte as $inhalt): ?>
                        <?php if ($inhalt['typ'] == 'erklaerung'): ?>
                        <div class="content-card">
                            <h2><?= htmlspecialchars($inhalt['titel']) ?></h2>
                            <?php if (!empty($inhalt['exercise_style'])): ?>
                            <div class="exercise-container" id="<?= 'exercise-' . $inhalt['id'] ?>">
                                <div class="math-exercise"
                                     data-style="<?= htmlspecialchars($inhalt['exercise_style']) ?>"
                                     data-latex="<?= htmlspecialchars($inhalt['inhalt']) ?>">
                                </div>
                            </div>
                            <?php else: ?>
                            <p><?= nl2br($inhalt['inhalt']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($inhalt['datei_pfad'])): ?>
                                <div class="erklaerung-bild" style="margin-top: 20px;">
                                    <img alt="../<?= htmlspecialchars($data_src_url . $inhalt['datei_pfad']) ?>" src="../<?= htmlspecialchars($data_src_url . $inhalt['datei_pfad']) ?>" style="max-width: 100%; height: auto;">
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($inhalt['verknuepfungen'])): ?>
                                <div class="verknuepfungen-section" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                                    <h3 style="font-size: 0.95em; color: #666; margin-bottom: 10px;">Zugehörige Inhalte:</h3>
                                    <div class="verknuepfungen-grid">
                                        <?php foreach ($inhalt['verknuepfungen'] as $verknuepfung): ?>
                                            <a href="javascript:void(0);"
                                               onclick="switchTab('<?= $verknuepfung['typ'] ?>')"
                                               class="verknuepfung-badge"
                                               title="<?= htmlspecialchars($verknuepfung['titel']) ?>">
                                                <?php if ($verknuepfung['typ'] == 'uebung'): ?>
                                                    <i class="fas fa-dumbbell"></i> <?= htmlspecialchars(substr($verknuepfung['titel'], 0, 30)) ?>...
                                                <?php elseif ($verknuepfung['typ'] == 'video'): ?>
                                                    <i class="fas fa-video"></i> <?= htmlspecialchars(substr($verknuepfung['titel'], 0, 30)) ?>...
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

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
        <section id="uebung" class="toggle-section <?= $active_tab == 'uebung' ? 'active' : '' ?>">
            <?php if (count(array_filter($lerninhalte, function($inhalt) { return $inhalt['typ'] == 'uebung'; })) > 0): ?>
                <?php foreach ($lerninhalte as $inhalt): ?>
                    <?php if ($inhalt['typ'] == 'uebung'): ?>
                        <div class="content-card">
                            <h2><?= htmlspecialchars($inhalt['titel']) ?></h2>
                            <p class="text_mittig"><?= nl2br($inhalt['inhalt']) ?></p>
                        <?php if (!empty($inhalt['datei_pfad'])): ?>
                            <?php
                            // Dateiendung prüfen
                            $ext = strtolower(pathinfo($inhalt['datei_pfad'], PATHINFO_EXTENSION));
                            $is_image = in_array($ext, ['jpg', 'jpeg', 'png']);
                            $icon_class = $is_image ? 'fa-file-image' : 'fa-file-pdf';
                            $text_label = $is_image ? 'Bild' : 'PDF';
                            ?>
                            <div class="uebung-grid">
                                <a class="uebung-card"
                                   href="../<?= htmlspecialchars($data_src_url . $inhalt['datei_pfad']) ?>"
                                   download>
                                    <i class="fa-solid <?= $icon_class ?>" style="font-size: 2em; margin-bottom: 10px;"></i>
                                    <div><strong><?= $text_label ?></strong><br><small>Download</small></div>
                                </a>
                                <!-- BuiltIn PDF-Viewer -->
                                <a class="uebung-card"
                                   href="../<?= htmlspecialchars($data_src_url . $inhalt['datei_pfad']) ?>"
                                   target="_blank">
                                    <i class="fa-solid <?= $icon_class ?>" style="font-size: 2em; margin-bottom: 10px;"></i>
                                    <div><strong><?= $text_label ?></strong><br><small>Ansicht</small></div> </a>

                            </div>
                        <?php endif; ?>

                        <?php if (!empty($inhalt['verknuepfungen'])): ?>
                            <div class="verknuepfungen-section" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                                <h3 style="font-size: 0.95em; color: #666; margin-bottom: 10px;">Zugehörige Inhalte:</h3>
                                <div class="verknuepfungen-grid">
                                    <?php foreach ($inhalt['verknuepfungen'] as $verknuepfung): ?>
                                        <a href="javascript:void(0);"
                                           onclick="switchTab('<?= $verknuepfung['typ'] ?>')"
                                           class="verknuepfung-badge"
                                           title="<?= htmlspecialchars($verknuepfung['titel']) ?>">
                                            <?php if ($verknuepfung['typ'] == 'erklaerung'): ?>
                                                <i class="fas fa-book"></i> <?= htmlspecialchars(substr($verknuepfung['titel'], 0, 30)) ?>...
                                            <?php elseif ($verknuepfung['typ'] == 'video'): ?>
                                                <i class="fas fa-video"></i> <?= htmlspecialchars(substr($verknuepfung['titel'], 0, 30)) ?>...
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

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
        <section id="video" class="toggle-section <?= $active_tab == 'video' ? 'active' : '' ?>">
            <?php if (count(array_filter($lerninhalte, function($inhalt) { return $inhalt['typ'] == 'video'; })) > 0): ?>
                <?php foreach ($lerninhalte as $inhalt): ?>
                    <?php if ($inhalt['typ'] == 'video'): ?>
                        <div class="content-card">
                            <h2><?= htmlspecialchars($inhalt['titel']) ?></h2>
                            <p><?= nl2br($inhalt['inhalt']) ?></p>
                            <?php if ($inhalt['video_url']): ?>
                                <div style="position:relative; padding-top:56.25%; border-radius: 10px; overflow: hidden; margin-top: 20px;">
                                    <iframe src="<?= htmlspecialchars($inhalt['video_url']) ?>"
                                            title="<?= htmlspecialchars($inhalt['titel']) ?>"
                                            style="position:absolute; left:0; top:0; width:100%; height:100%; border:0;"
                                            allowfullscreen></iframe>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($inhalt['verknuepfungen'])): ?>
                                <div class="verknuepfungen-section" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                                    <h3 style="font-size: 0.95em; color: #666; margin-bottom: 10px;">Zugehörige Inhalte:</h3>
                                    <div class="verknuepfungen-grid">
                                        <?php foreach ($inhalt['verknuepfungen'] as $verknuepfung): ?>
                                            <a href="javascript:void(0);"
                                               onclick="switchTab('<?= $verknuepfung['typ'] ?>')"
                                               class="verknuepfung-badge"
                                               title="<?= htmlspecialchars($verknuepfung['titel']) ?>">
                                                <?php if ($verknuepfung['typ'] == 'erklaerung'): ?>
                                                    <i class="fas fa-book"></i> <?= htmlspecialchars(substr($verknuepfung['titel'], 0, 30)) ?>...
                                                <?php elseif ($verknuepfung['typ'] == 'uebung'): ?>
                                                    <i class="fas fa-dumbbell"></i> <?= htmlspecialchars(substr($verknuepfung['titel'], 0, 30)) ?>...
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
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

    <footer>
        <div id = "imp"><a href="../impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>

    </footer>


    <?php include '../includes/accessibility.php'; ?>
    <script src="../js/accessibility.js"></script>
    <script src="../js/header-responsive.js"></script>

    <!-- /*----------------------------------MathJax E-Test---------------------------------- */ -->

    <script>// Initialer Aufruf: Warte, bis MathJax UND das DOM bereit sind
        document.addEventListener("DOMContentLoaded", () => {
            if (window.MathJax && MathJax.startup) {
                // Warte explizit auf das MathJax-Versprechen
                MathJax.startup.promise.then(() => {
                    initExercises();
                });
            } else {
                // Fallback, falls MathJax gar nicht lädt
                initExercises();
            }
        });

        function initExercises() {
            const exercises = document.querySelectorAll('.math-exercise');

            exercises.forEach(container => {
                const rawLatex = container.getAttribute('data-latex');
                const style = container.getAttribute('data-style'); // 'variables' oder 'blanks'

                // Regex um [[id:antwort]] zu finden
                // Gruppe 1: ID, Gruppe 2: Antwort
                const pattern = /\[\[(.*?):(.*?)\]\]/g;
                let solutions = {}; // Speichert ID -> Antwort


                let renderLatex = rawLatex;
                let inputsHtml = "";

                // 1. Parsing & Vorbereitung
                if (style === 'blanks') {
                    // Für Blanks ersetzen wir [[id:ans]] durch einen HTML Platzhalter
                    // MathJax erlaubt \class{name}{content} oder \cssId{id}{content}
                    // Wir nutzen cssId, um das Element später durch ein Input zu ersetzen.
                    renderLatex = rawLatex.replace(pattern, (match, id, ans) => {
                        solutions[id] = ans.trim();
                        // Wichtig: Wir nutzen cssId für das spätere Ersetzen
                        return `\\class{math-input-placeholder}{\\cssId{${id}}{\\fbox{\\phantom{ww}}}}`;
                    });
                } else if (style === 'variables') {
                    // VARIABLEN: Entferne die Annotation [[x:2]] komplett aus der Formel
                    // und bauen separate Inputs
                    renderLatex = rawLatex.replace(pattern, (match, id, ans) => {
                        solutions[id] = ans.trim();
                        return ''; // Wir löschen den Tag aus der visuellen Formel
                    });

                    // HTML für externe Inputs generieren
                    for (let [id, ans] of Object.entries(solutions)) {
                        inputsHtml += `
                    <div class="variable-input-row">
                        <label>$$${id} = $$</label>
                        <input type="text" class="math-input-inline" data-id="${id}" placeholder="?">
                    </div>
                `;
                    }
                }

                // Entscheidung: Controls (Überprüfen/Lösung anzeigen) nur wenn Zahlen/Rechnen
                let shouldShowControls = false;
                if (Object.keys(solutions).length > 0) {
                    // 1) Falls mindestens eine Lösung numerisch ist (Ganz-/Dezimalzahlen)
                    for (let ans of Object.values(solutions)) {
                        if (/^\s*-?\d+([\.,]\d+)?\s*$/.test(ans)) {
                            shouldShowControls = true;
                            break;
                        }
                    }

                    // 2) Falls noch nicht erkannt: prüfen, ob die Formel Rechenzeichen enthält (z.B. + - * / × ÷)
                    if (!shouldShowControls) {
                        const arithmeticPattern = /[+\-*/×÷=]/;
                        if (arithmeticPattern.test(renderLatex)) {
                            shouldShowControls = true;
                        }
                    }
                }

                // 2. SCHRITT: Den Inhalt in den Container schreiben (OHNE extra $$ außenrum)
                // Zeilenumbrüche aus der DB erhalten
                container.innerHTML = renderLatex.replace(/\n/g, '<br>');
                // // 2. Initiales Rendering in den Container
                // container.innerHTML = `$$${renderLatex}$$`;

                // Container für Buttons und externe Inputs
                const controlsDiv = document.createElement('div');
                controlsDiv.innerHTML = inputsHtml;

                // Buttons nur hinzufügen, wenn der Exercise-Style 'blanks' ist UND shouldShowControls true
                let btnGroup = null;
                if (style === 'blanks' && shouldShowControls) {
                    btnGroup = document.createElement('div');
                    btnGroup.className = 'btn-group';
                    btnGroup.innerHTML = `
                <button class="btn-check">Überprüfen</button>
                <button class="btn-solve">Lösung anzeigen</button>
            `;
                    controlsDiv.appendChild(btnGroup);
                }

                container.parentElement.appendChild(controlsDiv);

                // 3. MathJax Typesetting & Post-Processing (Inputs einfügen)
                MathJax.typesetPromise([container, controlsDiv]).then(() => {

                    if (style === 'blanks') {
                        // Jetzt suchen wir die Platzhalter in der gerenderten Formel und tauschen sie aus
                        for (let [id, ans] of Object.entries(solutions)) {
                            const placeholder = document.getElementById(id);
                            if (placeholder) {
                                // Erstelle das Input Feld
                                const input = document.createElement('input');
                                input.type = 'text';
                                input.className = 'math-input-inline';
                                input.dataset.id = id;
                                input.placeholder = "?"; // Optional

                                // Ersetze den MathJax Platzhalter durch das echte Input
                                placeholder.innerHTML = "";
                                placeholder.appendChild(input);
                                // CSS Anpassung, damit es inline fließt
                                placeholder.style.display = 'inline-block';
                            }
                        }
                    }

                    // Event Listener für Buttons nur anhängen, wenn btnGroup existiert
                    if (btnGroup) {
                        const btnCheck = btnGroup.querySelector('.btn-check');
                        const btnSolve = btnGroup.querySelector('.btn-solve');

                        if (btnCheck) btnCheck.addEventListener('click', () => checkAnswers(container.parentElement, solutions));
                        if (btnSolve) btnSolve.addEventListener('click', () => showSolutions(container.parentElement, solutions));
                    }
                });
            });
        }

        // Logik: Antworten prüfen
        function checkAnswers(wrapper, solutions) {
            const inputs = wrapper.querySelectorAll('input');
            let allCorrect = true;

            inputs.forEach(input => {
                const id = input.dataset.id;
                const correctVal = solutions[id];
                const userVal = input.value.trim();

                input.classList.remove('correct', 'wrong');

                if (userVal === correctVal) {
                    input.classList.add('correct');
                } else {
                    input.classList.add('wrong');
                    allCorrect = false;
                }
            });

            // Optional: Globales Feedback
            if(allCorrect) {
                // alert("Super, alles richtig!");
            }
        }

        // Logik: Lösung anzeigen (SOLL Anforderung)
        function showSolutions(wrapper, solutions) {
            const inputs = wrapper.querySelectorAll('input');
            inputs.forEach(input => {
                const id = input.dataset.id;
                if(solutions[id]) {
                    input.value = solutions[id];
                    input.classList.remove('wrong');
                    input.classList.add('correct');
                }
            });
        }
    </script>
    <!-- /*----------------------------------MathJax E-Test---------------------------------- */ -->
    <script src="../js/search-mobile.js"></script>
</body>
</html>



