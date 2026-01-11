<?php
session_start();
require_once "../../config/database.php";

// Nur für Admins zugänglich
if (!isset($_SESSION['angemeldet']) || $_SESSION['rolle'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

// Verhindere, dass der Admin sich selbst löscht
if ($user_id == $_SESSION['user_id']) {
    header("Location: accounts_uebersicht.php?message=Sie+können+sich+nicht+selbst+löschen.");
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Benutzername für Nachricht holen
$sql = "SELECT benutzername FROM benutzer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$benutzer = $stmt->fetch(PDO::FETCH_ASSOC);

if ($benutzer) {
    // Benutzer löschen
    $sql = "DELETE FROM benutzer WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);

    $message = "Account '{$benutzer['benutzername']}' wurde gelöscht.";
} else {
    $message = "Account nicht gefunden.";
}

$conn = null;
header("Location: accounts_uebersicht.php?message=" . urlencode($message));
exit;
?>