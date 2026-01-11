<?php

session_start();

// Prüfe, ob der Benutzer angemeldet und ein Admin/Lehrer ist
if (!isset($_SESSION['angemeldet']) || !in_array($_SESSION['rolle'], ['lehrer', 'admin'])) {
    header("Location: ../dashboard.php");
    exit;
}

// Variablen für Header
$isLoggedIn = isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
$username = $_SESSION['benutzername'] ?? '';
$userRole = $_SESSION['rolle'] ?? 'guest';

require_once "../../config/database.php";

$database = new Database();
$conn = $database->getConnection();

// Lerninhalt-ID aus URL
$lerninhalt_id = isset($_GET['lerninhalt_id']) ? intval($_GET['lerninhalt_id']) : 0;
if ($lerninhalt_id == 0) {
    header("Location: ../dashboard.php");
    exit;
}

// Aktuellen Lerninhalt laden
$stmt = $conn->prepare("SELECT l.*, u.id as unterthema_id FROM lerninhalte l 
                        JOIN unterthemen u ON l.unterthema_id = u.id 
                        WHERE l.id = ?");
$stmt->execute([$lerninhalt_id]);
$lerninhalt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lerninhalt) {
    header("Location: ../dashboard.php");
    exit;
}

$unterthema_id = $lerninhalt['unterthema_id'];

// Verknüpfung hinzufügen
if (isset($_POST['add_link'])) {
    $verknuepfter_id = intval($_POST['verknuepfter_id']);

    if ($verknuepfter_id > 0 && $verknuepfter_id != $lerninhalt_id) {
        // Prüfe, ob bereits existiert
        $stmt = $conn->prepare("SELECT id FROM lerninhalt_verknuepfungen WHERE lerninhalt_id = ? AND verknuepfter_lerninhalt_id = ?");
        $stmt->execute([$lerninhalt_id, $verknuepfter_id]);

        if (!$stmt->fetch()) {
            $stmt = $conn->prepare("INSERT INTO lerninhalt_verknuepfungen (lerninhalt_id, verknuepfter_lerninhalt_id) VALUES (?, ?)");
            $stmt->execute([$lerninhalt_id, $verknuepfter_id]);
            $success = "Verknüpfung hinzugefügt!";
        } else {
            $error = "Diese Verknüpfung existiert bereits!";
        }
    }
}

// Verknüpfung löschen
if (isset($_GET['delete_link'])) {
    $verknuepfter_id = intval($_GET['delete_link']);

    // Lösche beide Richtungen
    $stmt = $conn->prepare("DELETE FROM lerninhalt_verknuepfungen 
                           WHERE (lerninhalt_id = ? AND verknuepfter_lerninhalt_id = ?)
                           OR (lerninhalt_id = ? AND verknuepfter_lerninhalt_id = ?)");
    $stmt->execute([$lerninhalt_id, $verknuepfter_id, $verknuepfter_id, $lerninhalt_id]);

    header("Location: lerninhalt_verknuepfungen.php?lerninhalt_id=" . $lerninhalt_id);
    exit;
}

// Bestehende Verknüpfungen laden (bidirektional mit PHP)
$verknuepfungen_array = [];

// 1. Direkte Verknüpfungen: Was ist mit diesem Inhalt verknüpft?
$stmt = $conn->prepare("SELECT l.* FROM lerninhalte l 
                        JOIN lerninhalt_verknuepfungen v ON l.id = v.verknuepfter_lerninhalt_id 
                        WHERE v.lerninhalt_id = ?
                        ORDER BY l.typ, l.sort_order");
$stmt->execute([$lerninhalt_id]);
$direkteVerknuepfungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($direkteVerknuepfungen as $v) {
    $verknuepfungen_array[$v['id']] = $v;
}

// 2. Inverse Verknüpfungen: Andere Inhalte, die diesen verknüpft haben
$stmt = $conn->prepare("SELECT l.* FROM lerninhalte l 
                        JOIN lerninhalt_verknuepfungen v ON l.id = v.lerninhalt_id 
                        WHERE v.verknuepfter_lerninhalt_id = ?
                        ORDER BY l.typ, l.sort_order");
$stmt->execute([$lerninhalt_id]);
$inverseVerknuepfungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($inverseVerknuepfungen as $v) {
    $verknuepfungen_array[$v['id']] = $v;
}

$verknuepfungen = array_values($verknuepfungen_array);

// Alle anderen Lerninhalte des Unterthemas laden (für die Auswahl)
$stmt = $conn->prepare("SELECT * FROM lerninhalte WHERE unterthema_id = ? AND id != ? ORDER BY typ, sort_order");
$stmt->execute([$unterthema_id, $lerninhalt_id]);
$alle_inhalte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Aktuelles Unterthema laden
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
    <title>Verknüpfungen verwalten - <?= htmlspecialchars($lerninhalt['titel']) ?></title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .back-btn {
            background: #95a5a6;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #7f8c8d;
        }

        .success-msg {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .error-msg {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        h1 {
            color: #2c3e50;
            margin-top: 0;
        }

        .typ-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            color: white;
            margin-left: 10px;
        }

        .typ-badge.erklaerung {
            background: #3498db;
        }

        .typ-badge.uebung {
            background: #e74c3c;
        }

        .typ-badge.video {
            background: #f39c12;
        }

        .verknuepfung-item {
            background: #f8f9fa;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border-left: 4px solid #27ae60;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .verknuepfung-item a {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.9em;
        }

        .verknuepfung-item a:hover {
            background: #c0392b;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .form-group button {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .form-group button:hover {
            background: #229954;
        }

        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin: 30px 0 20px 0;
        }

        .no-links {
            color: #999;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="../../img/logo.png" alt="Logo">
    </button>
    <span class="page-title"><?= htmlspecialchars($aktuelles_thema['hauptfach_name'] ?? 'Unbekannt') ?> - <?= htmlspecialchars($aktuelles_thema['name'] ?? 'Unbekanntes Thema') ?></span>

    <div class="login-status">
        <a href="unterthema_bearbeiten.php?unterthema_id=<?= htmlspecialchars($unterthema_id) ?>" class="auth-btn">
            <i class="fas fa-arrow-left"></i> Zurück
        </a>
        <a href="../../logout.php" id="logout-btn" class="auth-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>

<?php include '../../includes/sidebar.php'; ?>

<main class="content">
    <div class="container">

        <h1>Verknüpfungen verwalten</h1>
        <p style="color: #666;">
            Für: <strong><?= htmlspecialchars($lerninhalt['titel']) ?></strong>
            <span class="typ-badge <?= $lerninhalt['typ'] ?>">
                <?= strtoupper($lerninhalt['typ']) ?>
            </span>
        </p>

        <?php if (isset($success)): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Bestehende Verknüpfungen -->
        <div class="section-title">Aktuelle Verknüpfungen</div>

        <?php if (count($verknuepfungen) > 0): ?>
            <?php foreach ($verknuepfungen as $verknuepfung): ?>
                <div class="verknuepfung-item">
                    <span>
                        <?= htmlspecialchars($verknuepfung['titel']) ?>
                        <span class="typ-badge <?= $verknuepfung['typ'] ?>">
                            <?= strtoupper($verknuepfung['typ']) ?>
                        </span>
                    </span>
                    <a href="lerninhalt_verknuepfungen.php?lerninhalt_id=<?= $lerninhalt_id ?>&delete_link=<?= $verknuepfung['id'] ?>" onclick="return confirm('Wirklich löschen?');">
                        <i class="fas fa-trash"></i> Löschen
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-links">
                Noch keine Verknüpfungen vorhanden.
            </div>
        <?php endif; ?>

        <!-- Neue Verknüpfung hinzufügen -->
        <div class="section-title">Neue Verknüpfung hinzufügen</div>

        <?php if (count($alle_inhalte) > 0): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="verknuepfter_id">Lerninhalt wählen:</label>
                    <select name="verknuepfter_id" id="verknuepfter_id" required>
                        <option value="">-- Bitte wählen --</option>
                        <?php foreach ($alle_inhalte as $inhalt): ?>
                            <option value="<?= $inhalt['id'] ?>">
                                <?= htmlspecialchars($inhalt['titel']) ?> (<?= strtoupper($inhalt['typ']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" name="add_link">
                        <i class="fas fa-plus"></i> Verknüpfung hinzufügen
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="no-links">
                Keine anderen Lerninhalte vorhanden, um Verknüpfungen zu erstellen.
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div id="imp"><a href="../../impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>

<?php include '../../includes/accessibility.php'; ?>
<script src="../../js/accessibility.js"></script>
<script src="../../js/search-mobile.js"></script>
</body>
</html>

