<?php

session_start();

// Prüfe, ob der Benutzer angemeldet und ein Admin ist
if (!isset($_SESSION['angemeldet']) || !in_array($_SESSION['rolle'], ['lehrer', 'admin'])) {
    header("Location: dashboard.php");
    exit;
}

// Variablen für Header
$isLoggedIn = isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
$username = $_SESSION['benutzername'] ?? '';
$userRole = $_SESSION['rolle'] ?? 'guest';

require_once "../../config/database.php";

$database = new Database();
$conn = $database->getConnection();

// Sicherstellen, dass die optionale Spalte exercise_style vorhanden ist (Migration-Fallback)
function ensureColumnExists($conn, $table, $column, $definition) {
    try {
        $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        $col = $stmt->fetch();
        if (!$col) {
            // Spalte fehlt, hinzufügen
            $sql = "ALTER TABLE `$table` ADD COLUMN $column $definition";
            $conn->exec($sql);
        }
    } catch (PDOException $e) {
        // Falls etwas schiefgeht, loggen wir es in error_log, aber Anwendung kann weiterlaufen
        error_log("Migration fallback failed for $table.$column: " . $e->getMessage());
    }
}

// Versuche die Spalte anzulegen falls sie noch nicht existiert
ensureColumnExists($conn, 'lerninhalte', 'exercise_style', "VARCHAR(20) DEFAULT 'blanks'");

// Unterthema-ID aus URL
$unterthema_id = isset($_GET['unterthema_id']) ? intval($_GET['unterthema_id']) : 1;
// typ aus URL
$active_tab = $_GET['tab'] ?? 'erklaerung';

// Bearbeitungs-ID (falls ein spezifischer Eintrag bearbeitet werden soll)
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;



// --- 2. LOGIK: VERSCHIEBEN (HOCH / RUNTER) ---
if (isset($_GET['move_id']) && isset($_GET['direction'])) {
    $move_id = intval($_GET['move_id']);
    $direction = $_GET['direction'];

    // 1. Infos des aktuellen Elements holen
    $stmt = $conn->prepare("SELECT id, sort_order FROM lerninhalte WHERE id = ?");
    $stmt->execute([$move_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current) {
        // 2. Tauschpartner finden
        if ($direction == 'up') {
            // Suche das Element direkt davor (kleinere sort_order)
            $sql = "SELECT id, sort_order FROM lerninhalte WHERE unterthema_id = ? AND typ = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1";
        } else {
            // Suche das Element direkt danach (größere sort_order)
            $sql = "SELECT id, sort_order FROM lerninhalte WHERE unterthema_id = ? AND typ = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute([$unterthema_id, $active_tab, $current['sort_order']]);
        $partner = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Wenn Partner gefunden, Plätze tauschen
        if ($partner) {
            // A bekommt B's Nummer
            $update1 = $conn->prepare("UPDATE lerninhalte SET sort_order = ? WHERE id = ?");
            $update1->execute([$partner['sort_order'], $current['id']]);

            // B bekommt A's Nummer
            $update2 = $conn->prepare("UPDATE lerninhalte SET sort_order = ? WHERE id = ?");
            $update2->execute([$current['sort_order'], $partner['id']]);
        }
    }

    // Redirect um URL zu säubern
    header("Location: unterthema_bearbeiten.php?unterthema_id=$unterthema_id&tab=$active_tab");
    exit;
}

// Lösch-Aktion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Zuerst Datei info holen um ggf. Datei zu löschen
    $stmt = $conn->prepare("SELECT datei_pfad FROM lerninhalte WHERE id = ?");
    $stmt->execute([$delete_id]);
    $delEntry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delEntry && !empty($delEntry['datei_pfad']) && file_exists($delEntry['datei_pfad'])) {
        unlink($delEntry['datei_pfad']); // Datei vom Server löschen
    }

    $stmt = $conn->prepare("DELETE FROM lerninhalte WHERE id = ?");
    $stmt->execute([$delete_id]);

    // Redirect um Neuladen zu verhindern
    header("Location: unterthema_bearbeiten.php?unterthema_id=$unterthema_id&tab=$active_tab&msg=deleted");
    exit;
}

$msg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $msg = "<div id='msg-box' style='color: green; margin-bottom: 15px;'>Eintrag erfolgreich gelöscht.</div>";
    } elseif ($_GET['msg'] == 'created') {
        $msg = "<div id='msg-box' style='color: green; margin-bottom: 15px;'>Neuer Eintrag erfolgreich erstellt!</div>";
    } elseif ($_GET['msg'] == 'updated') {
        $msg = "<div id='msg-box' style='color: green; margin-bottom: 15px;'>Änderungen erfolgreich gespeichert!</div>";
    }
}
$form_titel = "";
$form_inhalt = "";
$form_video_url = "";
$form_datei_pfad = "";
$is_edit_mode = false;


// Wenn edit_id gesetzt ist, Daten für das Formular laden
if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM lerninhalte WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_entry = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($edit_entry) {
        $form_titel = $edit_entry['titel'];
        $form_inhalt = $edit_entry['inhalt'];
        $form_video_url = $edit_entry['video_url'];
        $form_datei_pfad = $edit_entry['datei_pfad'];
        $is_edit_mode = true;
    }
}

// --- SPEICHERN (INSERT oder UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = $_POST['titel'];
    $inhalt = $_POST['inhalt'];
    $video_url = $_POST['video_url'] ?? null;
    $current_id = $_POST['current_id'] ?? null; // ID aus verstecktem Feld

    $exercise_style = $_POST['exercise_style'] ?? 'blanks';

    // Datei Upload Logik
    $targetPath = null;
    // Wenn Update, behalte alten Pfad als Standard
    if ($is_edit_mode) {
        $targetPath = $form_datei_pfad;

        // --- NEU: Logik zum Löschen der Datei per Checkbox ---
        if (isset($_POST['delete_pdf']) && $_POST['delete_pdf'] == '1') {
            // Physische Datei löschen
            if (!empty($form_datei_pfad) && file_exists($form_datei_pfad)) {
                unlink($form_datei_pfad);
            }
            // Pfad für die Datenbank leeren
            $targetPath = null;
        }
        // ----------------------------------------------------
    }
    if (isset($_FILES['pdf_datei']) && $_FILES['pdf_datei']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = basename($_FILES['pdf_datei']['name']);
        $newPath = $uploadDir . uniqid() . "_" . $fileName;

        if (move_uploaded_file($_FILES['pdf_datei']['tmp_name'], $newPath)) {
            // Alte Datei löschen, falls wir im Edit mode sind und eine existierte
            if ($is_edit_mode && !empty($form_datei_pfad) && file_exists($form_datei_pfad)) {
                unlink($form_datei_pfad);
            }
            $targetPath = $newPath;
        } else {
            $msg = "<div style='color: red;'>Fehler beim Upload.</div>";
        }
    }

    if (empty($msg)) {
        if ($current_id) {
            // --- UPDATE mit exercise_style ---
            $sql = "UPDATE lerninhalte SET titel = ?, inhalt = ?, video_url = ?, datei_pfad = ?, exercise_style = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$titel, $inhalt, $video_url, $targetPath, $exercise_style, $current_id])) {
                header("Location: unterthema_bearbeiten.php?unterthema_id=$unterthema_id&tab=$active_tab&msg=updated");
                exit;
            }
        } else {
            // --- INSERT mit exercise_style ---
            $stmt = $conn->prepare("SELECT MAX(sort_order) as max_sort FROM lerninhalte WHERE unterthema_id = ? AND typ = ?");
            $stmt->execute([$unterthema_id, $active_tab]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_sort = ($row['max_sort'] !== null) ? $row['max_sort'] + 1 : 1;

            $sql = "INSERT INTO lerninhalte (unterthema_id, typ, titel, inhalt, video_url, datei_pfad, sort_order, exercise_style) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$unterthema_id, $active_tab, $titel, $inhalt, $video_url, $targetPath, $next_sort, $exercise_style])) {
                header("Location: unterthema_bearbeiten.php?unterthema_id=$unterthema_id&tab=$active_tab&msg=created");
                exit;
            }
        }
    }
}

// --- LISTE ALLER INHALTE LADEN (für die Übersichtstabelle) ---
$stmt = $conn->prepare("SELECT * FROM lerninhalte WHERE unterthema_id = ? AND typ = ? ORDER BY sort_order ASC");
$stmt->execute([$unterthema_id, $active_tab]);
$alle_inhalte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Falls sort_order überall 0 ist (alte Daten), reparieren wir das on-the-fly für die Anzeige
$needs_fix = false;
foreach($alle_inhalte as $k => $v) { if($v['sort_order'] == 0 && $k > 0) $needs_fix = true; }
if($needs_fix) {
    // Einmalig durchnummerieren in der DB, damit die Pfeile funktionieren
    foreach($alle_inhalte as $index => $item) {
        $new_sort = $index + 1;
        $conn->prepare("UPDATE lerninhalte SET sort_order = ? WHERE id = ?")->execute([$new_sort, $item['id']]);
        $alle_inhalte[$index]['sort_order'] = $new_sort; // Array auch updaten
    }
}

// Aktuelles Unterthema laden (Header Info)
$sql = "SELECT u.*, h.name as hauptfach_name FROM unterthemen u JOIN hauptfaecher h ON u.hauptfach_id = h.id WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$unterthema_id]);
$aktuelles_thema = $stmt->fetch(PDO::FETCH_ASSOC);

// Sidebar Daten (Standard)
$sql = "SELECT h.*, (SELECT COUNT(*) FROM unterthemen WHERE hauptfach_id = h.id) as unterthemen_count FROM hauptfaecher h ORDER BY h.sort_order";
$stmt = $conn->prepare($sql);
$stmt->execute();
$hauptfaecher = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($hauptfaecher as &$fach) {
    $stmt = $conn->prepare("SELECT * FROM unterthemen WHERE hauptfach_id = ? ORDER BY sort_order");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Thema') ?> - Bearbeiten</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- NEU: Style für die einzelnen Karten im Admin-Bereich --- */
        .admin-card-item {
            background-color: rgba(213, 213, 213, 0.47); /* Ganz leichtes Grau/Weiß */
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .admin-card-item:hover {
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .admin-card-item h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .admin-card-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        /* --------------------------------------------------------- */
        .edit-container {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        textarea {
            height: 120px;
        }
        .btn-save {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .btn-cancel {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }


        /* Tabelle für existierende Einträge */
        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .entries-table th, .entries-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .entries-table th {
            background-color: #f8f9fa;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            color: white;
            font-size: 0.85em;
            margin-right: 5px;
        }
        .btn-edit {
            background-color: #3498db;
        }
        .btn-delete {
            background-color: #e74c3c;
        }

        .section-title {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px; margin-bottom:
                20px; color: #333;
        }
        .form-box {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        /* Bearbeiten-Style */

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        textarea {
            height: 150px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        .bearbeiten{
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
        }

        /* Sortier-Buttons */
        .btn-sort { background-color: #95a5a6; padding: 6px 10px; color: white; border-radius: 5px; text-decoration: none; font-size: 0.9em; }
        .btn-sort:hover { background-color: #7f8c8d; }
        .btn-sort.disabled { opacity: 0.3; pointer-events: none; cursor: default; }
        .admin-sort-controls { display: flex; gap: 5px; justify-content: center; margin-top: 10px}
        .admin-card-footer { display: block; justify-content: space-between; align-items: center; margin-top: 5px; }
        /* Ende Bearbeiten-Style */



        /*--------------Editor mit LaTeX-------------------*/
        /* Editor Wrapper */
        .smart-editor {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        /* Toolbar oben */
        .editor-toolbar {
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
            padding: 8px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .editor-btn {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .editor-btn:hover { background: #e0e0e0; }

        /* Split Screen */
        .editor-area {
            display: flex;
            height: 500px; /* Feste Höhe für den Editor */
            background: #fafafa;
        }

        /* Eingabebereich */
        .editor-textarea {
            flex: 1;
            border: none;
            border-right: 1px solid #ddd;

            font-family: 'Courier New', monospace; /* Monospace hilft beim Coden */
            resize: none;
            font-size: 14px;
            outline: none;
            height: 100%;
            text-align: center !important;

        }

        /* Vorschau Bereich */
        .editor-preview {
            flex: 1;
            padding: 15px;
            background: #fafafa;
            overflow-y: auto;
            font-family: sans-serif;
            text-align: center !important;
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
            display: inline-block; /* Wichtig für Layout */
        }

        /* Stil für Inputs UNTER der Formel (Variablen) */
        .variable-input-row {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: sans-serif;
        }
        /*--------------Editor mit LaTeX-------------------*/

        /* Mobile responsive für Buttons */
        @media (max-width: 768px) {
            .admin-card-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .action-btn {
                width: 100%;
                padding: 10px 15px;
                text-align: center;
                margin-right: 0;
                margin-bottom: 5px;
            }
        }

        /* ==================== DARK MODE STYLES ==================== */
        body.dark-mode .edit-container {
            background: #2a2a2a;
            color: #e0e0e0;
        }

        body.dark-mode .admin-card-item {
            background-color: #2a2a2a;
            border-color: #444;
            color: #e0e0e0;
        }

        body.dark-mode .admin-card-item h3 {
            color: #ffffff;
            border-bottom-color: #444;
        }

        body.dark-mode input[type="text"],
        body.dark-mode textarea {
            background-color: #3a3a3a;
            color: #ffffff;
            border-color: #555;
        }

        body.dark-mode input[type="text"]:focus,
        body.dark-mode textarea:focus {
            background-color: #3a3a3a;
            color: #ffffff;
            border-color: #2555a8;
            outline: none;
        }

        body.dark-mode input[type="text"]::placeholder,
        body.dark-mode textarea::placeholder {
            color: #999;
        }

        body.dark-mode label {
            color: #e0e0e0;
        }

        body.dark-mode .form-box {
            background: #2a2a2a;
            border-color: #444;
            color: #e0e0e0;
        }

        body.dark-mode .entries-table {
            color: #e0e0e0;
        }

        body.dark-mode .entries-table th {
            background-color: #1a1a1a;
            color: #e0e0e0;
            border-bottom-color: #444;
        }

        body.dark-mode .entries-table td {
            border-bottom-color: #444;
        }

        body.dark-mode .section-title {
            border-bottom-color: #444;
            color: #e0e0e0;
        }

        body.dark-mode .smart-editor {
            border-color: #444;
            background: #2a2a2a;
        }

        body.dark-mode .editor-toolbar {
            background: #1a1a1a;
            border-bottom-color: #444;
        }

        body.dark-mode .editor-btn {
            background: #3a3a3a;
            border-color: #555;
            color: #e0e0e0;
        }

        body.dark-mode .editor-btn:hover {
            background: #4a4a4a;
        }

        body.dark-mode .editor-textarea {
            background: #3a3a3a;
            color: #ffffff;
            border-right-color: #444;
        }

        body.dark-mode .editor-preview {
            background: #2a2a2a;
            color: #e0e0e0;
        }

        body.dark-mode .math-input-inline {
            background-color: #3a3a3a;
            color: #ffffff;
            border-color: #555;
        }

        body.dark-mode .btn-save {
            background: #27ae60;
        }

        body.dark-mode .btn-save:hover {
            background: #219653;
        }

        body.dark-mode .btn-cancel {
            background: #e74c3c;
        }

        body.dark-mode .btn-cancel:hover {
            background: #c0392b;
        }

        body.dark-mode .btn-sort {
            background-color: #555;
        }

        body.dark-mode .btn-sort:hover {
            background-color: #666;
        }

        body.dark-mode #msg-box {
            color: #90ee90;
            background-color: #1e4620;
        }
    </style>

    <!-- MathJax  -->
    <script>
        window.MathJax = {
            loader: { load: ['[tex]/html'] }, // Wichtig für \class und \cssId
            tex: {
                packages: { '[+]': ['html'] },
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$','$$'], ['\\[','\\]']]
            },
            svg: { fontCache: 'global' }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <!-- MathJax  -->

</head>
<body>

<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title"><?= htmlspecialchars($aktuelles_thema['hauptfach_name'] ?? 'Unbekannt') ?> - <?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?></span>

    <div class="login-status">
        <a href="../unterthema.php?unterthema_id=<?= htmlspecialchars($unterthema_id) ?>" class="auth-btn">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
        <a href="../../logout.php" id="logout-btn" class="auth-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<?php include '../../includes/sidebar.php'; ?>

<main class="content">
    <h1><?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?></h1>
    <!-- Toggle-Bar -->
    <?php
    include '../../includes/toggle_bar.php';
    ?>



    <div class="edit-container">
        <?= $msg ?>

        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
            Vorhandene Inhalte (<?= count($alle_inhalte) ?>)
        </h3>

        <?php if (count($alle_inhalte) > 0): ?>

            <div class="admin-cards-container">

                <?php foreach ($alle_inhalte as $index => $eintrag): ?>

                    <div class="admin-card-item">

                            <h3><?= htmlspecialchars($eintrag['titel']) ?></h3>
                            <p style="color: #666; font-size: 0.9em;"><?= nl2br($eintrag['inhalt']) ?></p>
                        <?php if (!empty($eintrag['datei_pfad'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($eintrag['datei_pfad'], PATHINFO_EXTENSION));
                            $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                            ?>

                            <?php if ($is_image): ?>
                                <div style="margin-bottom: 15px;">
                                    <img src="<?= htmlspecialchars($eintrag['datei_pfad']) ?>"
                                         alt="Vorschau"
                                         style="max-width: 100%; height: auto; max-height: 150px; border-radius: 5px; border: 1px solid #ddd;">
                                </div>
                            <?php else: ?>
                                <div style="font-size: 0.85em; color: #e67e22; margin-bottom: 10px;">
                                    <i class="fa-solid fa-file-pdf"></i> PDF / Datei vorhanden
                                </div>
                            <?php endif; ?>
                        <?php elseif ($eintrag['typ'] == 'video' && !empty($eintrag['video_url'])): ?>

                            <div style="font-size: 0.85em; color: #e74c3c; margin-bottom: 10px;">
                                <i class="fa-brands fa-youtube"></i> Video verlinkt
                            </div>

                        <?php endif; ?>
                        <div class="admin-card-footer">

                            <div class="button_mittig">

                                <div class="admin-card-actions">


                                    <a href="?unterthema_id=<?= $unterthema_id ?>&tab=<?= $active_tab ?>&edit_id=<?= $eintrag['id'] ?>#form-anchor"
                                       class="action-btn btn-edit button_mittig">
                                        <i class="fa-solid fa-pen"></i> Bearbeiten
                                    </a>

                                    <a href="lerninhalt_verknuepfungen.php?lerninhalt_id=<?= $eintrag['id'] ?>"
                                       class="action-btn btn-link button_mittig"
                                       style="background-color: #27ae60;">
                                        <i class="fa-solid fa-link"></i> Verknüpfungen
                                    </a>

                                    <a href="?unterthema_id=<?= $unterthema_id ?>&tab=<?= $active_tab ?>&delete_id=<?= $eintrag['id'] ?>"
                                        class="action-btn btn-delete button_mittig"
                                            onclick="return confirm('Möchtest du diesen Eintrag wirklich unwiderruflich löschen?');">
                                            <i class="fa-solid fa-trash"></i> Löschen
                                    </a>

                                </div>

                            </div>

                            <div class="admin-sort-controls">

                                <a href="?unterthema_id=<?= $unterthema_id ?>&tab=<?= $active_tab ?>&move_id=<?= $eintrag['id'] ?>&direction=up"
                                   class="btn-sort <?= ($index === 0) ? 'disabled' : '' ?>" title="Nach oben">
                                    <i class="fa-solid fa-arrow-up"></i>
                                </a>

                                <a href="?unterthema_id=<?= $unterthema_id ?>&tab=<?= $active_tab ?>&move_id=<?= $eintrag['id'] ?>&direction=down"
                                   class="btn-sort <?= ($index === count($alle_inhalte) - 1) ? 'disabled' : '' ?>" title="Nach unten">
                                    <i class="fa-solid fa-arrow-down"></i>
                                </a>

                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #7f8c8d; font-style: italic; margin-bottom: 30px;">
                Noch keine Inhalte für diesen Bereich erstellt.
            </p>
        <?php endif; ?>


        <div id="form-anchor"></div>

        <div class="form-section <?= $is_edit_mode ? 'edit-active' : '' ?>">
            <h3 class="form-title" onclick="toggleForm()" style="cursor: pointer; user-select: none; display: flex; align-items: center; gap: 10px;">
                <?php if ($is_edit_mode): ?>
                    <i class="fa-solid fa-pen-to-square"></i> Eintrag bearbeiten: "<?= htmlspecialchars($form_titel) ?>"
                <?php else: ?>
                    <i class="fa-solid fa-plus-circle"></i> Neuen Inhalt hinzufügen
                <?php endif; ?>
            </h3>

            <div id="form-content" style="display: <?= $is_edit_mode ? 'block' : 'none' ?>; cursor: pointer; user-select: none; align-items: center; gap: 10px;">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($is_edit_mode): ?>
                    <input type="hidden" name="current_id" value="<?= $edit_id ?>">
                <?php endif; ?>

                <label for="titel">Titel:</label>
                <input type="text" id="titel" name="titel" value="<?= htmlspecialchars($form_titel) ?>" required placeholder="Überschrift eingeben...">

                <label for="inhalt">Inhaltstext / Beschreibung:</label>

                <div class="smart-editor">
                    <div class="editor-toolbar">
                        <button type="button" class="editor-btn" onclick="insertTag('<b>', '</b>')" title="Fett"><b>B</b></button>
                        <button type="button" class="editor-btn" onclick="insertTag('<i>', '</i>')" title="Kursiv"><i>I</i></button>
                        <div style="width: 1px; background: #ccc; margin: 0 5px;"></div>

                        <button type="button" class="editor-btn" onclick="insertTag('$', '$')" title="Inline Mathe">
                            <i class="fas fa-square-root-alt"></i> Inline
                        </button>
                        <button type="button" class="editor-btn" onclick="insertTag('$$', '$$')" title="Block Mathe">
                            <i class="fas fa-superscript"></i> Block
                        </button>

                        <div style="width: 1px; background: #ccc; margin: 0 5px;"></div>

                        <button type="button" class="editor-btn" onclick="insertPlaceholder()" style="color: #2980b9;">
                            <i class="fas fa-question-circle"></i> Antwort [[?]]
                        </button>

                        <?php
                        // Den gespeicherten Stil laden (oder Standard 'blanks')
                        $current_style = isset($edit_entry['exercise_style']);
                        ?>
                        <select name="exercise_style" id="preview-mode" class="editor-btn" style="margin-left: auto;" onchange="updatePreview()">
                            <option value="blanks" <?= $current_style === 'blanks' ? 'selected' : '' ?>>Vorschau: Lückentext</option>
                            <option value="variables" <?= $current_style === 'variables' ? 'selected' : '' ?>>Vorschau: Variablen</option>
                        </select>
                    </div>

                    <div class="editor-area">
                        <textarea id="inhalt" name="inhalt" class="editor-textarea"
                            placeholder="Schreibe hier deinen Text... Nutze $ für Formeln.">
                            <?= htmlspecialchars($form_inhalt) ?>
                        </textarea>

                        <div id="live-preview" class="editor-preview math-exercise" data-style="blanks">
                        </div>
                    </div>
                </div>

                <?php if ($active_tab == 'uebung' || $active_tab == 'erklaerung'): ?>
                    <label style="margin-top: 15px;">
                        <?= ($active_tab == 'erklaerung') ? 'Bild zur Erklärung hinzufügen:' : 'Übungsblatt / Datei:' ?>
                    </label>
                    <?php if ($is_edit_mode && !empty($form_datei_pfad)): ?>
                        <div style="margin-bottom: 10px; padding: 10px; background: #eee; border-radius: 5px; border: 1px solid #ddd;">
                            <div>
                                Aktuelle Datei: <a href="<?= htmlspecialchars($form_datei_pfad) ?>" target="_blank" style="font-weight: bold;"><?= basename($form_datei_pfad) ?></a>
                            </div>

                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #ccc;">
                                <label style="display: inline-flex; align-items: center; color: #c0392b; font-weight: normal; margin-top: 0; cursor: pointer;">
                                    <input type="checkbox" name="delete_pdf" value="1" style="width: auto; margin: 0 8px 0 0;">
                                    <i class="fa-solid fa-trash-can" style="margin-right: 5px;"></i> Diese Datei entfernen
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="pdf_datei" name="pdf_datei" accept=".pdf, .jpg, .jpeg, .png"> <?php elseif ($active_tab == 'video'): ?>
                    <label for="video_url">Video-URL (YouTube Embed Link):</label>
                    <input type="text" id="video_url" name="video_url" value="<?= htmlspecialchars($form_video_url) ?>" placeholder="https://www.youtube.com/embed/..." required>
                <?php endif; ?>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> <?= $is_edit_mode ? 'Änderungen speichern' : 'Hinzufügen' ?>
                    </button>

                    <?php if ($is_edit_mode): ?>
                        <a href="?unterthema_id=<?= $unterthema_id ?>&tab=<?= $active_tab ?>" class="btn-cancel button_mittig">Abbrechen</a>
                    <?php endif; ?>
                </div>
            </form>
            </div>
        </div>

</main>

<footer>
    <div id="imp"><a href="../../impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>

<script>

    // NEUE FUNKTION: Formular auf/zuklappen
    function toggleForm() {
        var formContent = document.getElementById('form-content');
        // --- NEU: URL bereinigen und Nachricht ausblenden ---
        // 1. Nachricht optisch ausblenden
        var msgBox = document.getElementById('msg-box');
        if (msgBox) {
            msgBox.style.display = 'none';
        }

        // 2. 'msg' Parameter aus der URL entfernen (ohne Neuladen)
        var url = new URL(window.location.href);
        if (url.searchParams.has('msg')) {
            url.searchParams.delete('msg');
            window.history.replaceState({}, document.title, url);
        }
        // ----------------------------------------------------


        // Prüfen ob es gerade unsichtbar ist
        if (formContent.style.display === 'none') {
            formContent.style.display = 'block';

        } else {
            // Formular VERSTECKEN
            formContent.style.display = 'none';
        }
    }
</script>

<script>
    // --- KONFIGURATION & REFERENZEN ---
    const textarea = document.getElementById('inhalt');
    const preview = document.getElementById('live-preview');
    const modeSelect = document.getElementById('preview-mode');

    // Debounce Timer (damit nicht bei jedem Tastenschlag gerendert wird)
    let debounceTimer;

    // --- 1. EVENT LISTENER ---
    textarea.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            updatePreview();
        }, 300); // 300ms warten nach dem Tippen
    });

    // Initialer Aufruf: Warte, bis MathJax UND das DOM bereit sind
    document.addEventListener("DOMContentLoaded", () => {
        if (window.MathJax && MathJax.startup) {
            // Warte explizit auf das MathJax-Versprechen
            MathJax.startup.promise.then(() => {
                updatePreview();
            });
        } else {
            // Fallback, falls MathJax gar nicht lädt
            updatePreview();
        }
    });
    // --- 2. CORE LOGIK: VORSCHAU RENDERN ---
    function updatePreview() {
        const rawText = textarea.value;
        const mode = modeSelect.value; // 'blanks' oder 'variables'

        // Wir simulieren hier exakt das Verhalten der Schüler-Seite.
        // Wir nutzen "renderMathExercise" für den Preview-Container.
        renderMathExercise(preview, rawText, mode);
    }

    /**
     * Diese Funktion ist das Herzstück. Sie ist identisch zur Logik im Frontend,
     * wurde aber so angepasst, dass sie einen spezifischen Container (target) neu rendert.
     */
    function renderMathExercise(targetContainer, rawLatex, style) {
        // 1. Text säubern (Linebreaks für HTML)
        // Wenn kein LaTeX Block da ist, machen wir einfache Breaks.
        // Aber wir behandeln hier alles als "Potenzielle Aufgabe".

        // Regex für deine Annotation [[id:antwort]]
        const pattern = /\[\[(.*?):(.*?)]\]/g;
        let solutions = {};
        let inputsHtml = "";

        // HTML-Safe machen (außer unsere LaTeX Tags)
        let processedLatex = rawLatex.replace(/\n/g, "<br>");

        // 2. PARSING (Identisch zu Schritt 1)
        if (style === 'blanks') {
            // LÜCKENTEXT: Ersetze [[id:ans]] durch MathJax HTML-ID Platzhalter
            processedLatex = processedLatex.replace(pattern, (match, id, ans) => {
                // ID säubern (keine Leerzeichen)
                id = id.trim();
                solutions[id] = ans.trim();
                // Platzhalter Box
                return `\\class{math-input-placeholder}{\\cssId{${id}}{\\fbox{\\phantom{ww}}}}`;
            });
        } else {
            // VARIABLEN: Entferne Annotation aus dem Text
            processedLatex = processedLatex.replace(pattern, (match, id, ans) => {
                id = id.trim();
                solutions[id] = ans.trim();
                return ""; // Löschen aus der visuellen Formel
            });

            // Inputs für unten generieren
            if(Object.keys(solutions).length > 0) {
                inputsHtml += '<div style="margin-top:15px; border-top:1px dashed #ccc; padding-top:10px;"><b>Eingabefelder:</b><br>';
                for (let [id, ans] of Object.entries(solutions)) {
                    inputsHtml += `
                    <div class="variable-input-row" style="margin:5px 0;">
                        <label style="display:inline;">$${id} = $</label>
                        <input type="text" class="math-input-inline" value="${ans}" disabled style="background:#eee; border:1px solid #ccc; width:60px; text-align:center;">
                        <small style="color:green;">(Lösung: ${ans})</small>
                    </div>
                `;
                }
                inputsHtml += '</div>';
            }
        }

        // 3. IN DOM SCHREIBEN
        // Hinweis: Wir wrappen nicht alles in $$, da der User vielleicht auch normalen Text schreibt.
        // Der User muss $...$ selbst setzen, aber unsere Platzhalter funktionieren nur IN MathJax.
        // TRICK: Wenn der User [[..]] benutzt, gehen wir davon aus, dass er gerade eine Formel schreibt
        // oder wir zwingen MathJax, nach IDs zu suchen.

        targetContainer.innerHTML = processedLatex + inputsHtml;

        // 4. MATHJAX RENDERING TRIGGERN
        if (window.MathJax) {
            MathJax.typesetPromise([targetContainer]).then(() => {
                // 5. POST-PROCESSING (Nur für Blanks: Inputs injizieren)
                if (style === 'blanks') {
                    for (let [id, ans] of Object.entries(solutions)) {
                        const placeholder = document.getElementById(id);
                        if (placeholder) {
                            placeholder.innerHTML = ""; // Box löschen

                            // Input erstellen (im Admin Modus deaktiviert, aber sichtbar)
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'math-input-inline';
                            input.value = ans; // Wir zeigen die Lösung direkt an im Editor!
                            input.disabled = true; // Nicht editierbar in der Vorschau
                            input.style.backgroundColor = "#eafaf1"; // Leicht grün, damit man sieht es ist die Lösung
                            input.style.border = "1px solid #2ecc71";
                            input.style.textAlign = "center";
                            input.style.width = "60px";

                            placeholder.appendChild(input);
                            placeholder.style.display = 'inline-block';
                        }
                    }
                }
            }).catch((err) => console.log('MathJax Fehler (normal beim Tippen): ', err));
        }
    }

    // --- 3. EDITOR HELPER TOOLS ---

    function insertTag(start, end) {
        const s = textarea.selectionStart;
        const e = textarea.selectionEnd;
        const val = textarea.value;
        textarea.value = val.substring(0, s) + start + val.substring(s, e) + end + val.substring(e);
        textarea.selectionStart = s + start.length;
        textarea.selectionEnd = e + start.length;
        textarea.focus();
        updatePreview();
    }

    function insertPlaceholder() {
        const id = prompt("Variablen-Name (z.B. x):", "x");
        if(!id) return;
        const ans = prompt("Lösungswert:", "0");
        if(ans === null) return;

        // Einfügen
        const tag = `$`+`[[${id}:${ans}]]`+`$`;
        insertTag(tag, "");
    }
</script>


<?php include '../../includes/accessibility.php'; ?>
<script src="../../js/accessibility.js"></script>
<script src="../../js/search-mobile.js"></script>

</body>
</html>


