<?php
echo "Verwendetes PHP: " . PHP_BINDIR . "<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "MySQLi verfügbar: " . (extension_loaded('mysqli') ? '✅ JA' : '❌ NEIN') . "<br>";

if (extension_loaded('mysqli')) {
    echo "✅ MySQLi ist geladen!";
} else {
    echo "❌ MySQLi ist NICHT geladen!";
}
?><?php
echo "Teste verschiedene Passwörter:<br><br>";

// Test 1: Leeres Passwort
$conn1 = new mysqli("localhost", "root", "", "lernwebseite");
if ($conn1->connect_error) {
    echo "❌ Leeres Passwort fehlgeschlagen: " . $conn1->connect_error . "<br>";
} else {
    echo "✅ Leeres Passwort FUNKTIONIERT!<br>";
    $conn1->close();
}

// Test 2: Passwort "1234"
$conn2 = new mysqli("localhost", "root", "1234", "lernwebseite");
if ($conn2->connect_error) {
    echo "❌ Passwort '1234' fehlgeschlagen: " . $conn2->connect_error . "<br>";
} else {
    echo "✅ Passwort '1234' FUNKTIONIERT!<br>";
    $conn2->close();
}

// Test 3: Ohne Passwort-Parameter
$conn3 = new mysqli("localhost", "root", null, "lernwebseite");
if ($conn3->connect_error) {
    echo "❌ Null-Passwort fehlgeschlagen: " . $conn3->connect_error . "<br>";
} else {
    echo "✅ Null-Passwort FUNKTIONIERT!<br>";
    $conn3->close();
}
?>