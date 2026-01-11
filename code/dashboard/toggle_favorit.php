<?php
session_start();
require_once "config/database.php";


// Login required
if (!isset($_SESSION['angemeldet'])) {
    header("Location: dashboard.php");
    exit;
}


$db = new Database();
$conn = $db->getConnection();
$userId = $_SESSION['user_id'];

/* HAUPTFACH TOGGLE */
if (isset($_POST['hauptfach_id'])) {
    $hauptfachId = (int) $_POST['hauptfach_id'];

    $check = $conn->prepare(
        "SELECT id FROM favoriten WHERE benutzer_id = ? AND hauptfach_id = ?"
    );
    $check->execute([$userId, $hauptfachId]);

    if ($check->fetch()) {
        $del = $conn->prepare(
            "DELETE FROM favoriten WHERE benutzer_id = ? AND hauptfach_id = ?"
        );
        $del->execute([$userId, $hauptfachId]);
    } else {
        $add = $conn->prepare(
            "INSERT INTO favoriten (benutzer_id, hauptfach_id) VALUES (?, ?)"
        );
        $add->execute([$userId, $hauptfachId]);
    }
}

/* UNTERTHEMA TOGGLE */
if (isset($_POST['unterthema_id'])) {
    $unterthemaId = (int) $_POST['unterthema_id'];

    $check = $conn->prepare(
        "SELECT id FROM favoriten_unterthemen WHERE benutzer_id = ? AND unterthema_id = ?"
    );
    $check->execute([$userId, $unterthemaId]);

    if ($check->fetch()) {
        $del = $conn->prepare(
            "DELETE FROM favoriten_unterthemen WHERE benutzer_id = ? AND unterthema_id = ?"
        );
        $del->execute([$userId, $unterthemaId]);
    } else {
        $add = $conn->prepare(
            "INSERT INTO favoriten_unterthemen (benutzer_id, unterthema_id)
             VALUES (?, ?)"
        );
        $add->execute([$userId, $unterthemaId]);
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
