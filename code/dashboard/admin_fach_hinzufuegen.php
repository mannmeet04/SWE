<?php
require_once "config/database.php";

if ($_POST) {
    $database = new Database();
    $conn = $database->getConnection();

    $name = $_POST['name'];
    $beschreibung = $_POST['beschreibung'];
    $bild = $_POST['bild'];
    $icon = $_POST['icon'];

    $sql = "INSERT INTO faecher (name, beschreibung, bild, icon, parent_id) VALUES (?, ?, ?, ?, NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $beschreibung, $bild, $icon]); // PDO execute mit Array

    echo "Fach erfolgreich hinzugefügt!";
    $conn = null;
}
?>

<form method="POST">
    <h2>Neues Fach hinzufügen</h2>
    <input type="text" name="name" placeholder="Fachname" required>
    <textarea name="beschreibung" placeholder="Beschreibung"></textarea>
    <input type="text" name="bild" placeholder="Bild-Pfad (img/...)">
    <input type="text" name="icon" placeholder="Icon (fas fa-...)">
    <button type="submit">Hinzufügen</button>
</form>

<form method="POST">
    <h2>Neues Fach hinzufügen</h2>
    <input type="text" name="name" placeholder="Fachname" required>
    <textarea name="beschreibung" placeholder="Beschreibung"></textarea>
    <input type="text" name="bild" placeholder="Bild-Pfad (img/...)">
    <input type="text" name="icon" placeholder="Icon (fas fa-...)">
    <button type="submit">Hinzufügen</button>
</form>