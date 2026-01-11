<?php
include('password_functions.php');

$passwords = [
    'admin' => 'Herr Admin',
    'schueler123' => 'Max Schüler',
    'lehrer123' => 'Frau Lehrer'
];

echo "Gehashte Passwörter für SQL INSERT:<br><br>";
echo "INSERT INTO benutzer (benutzername, email, passwort_hash, rolle) VALUES<br>";

$counter = 0;
foreach ($passwords as $password => $name) {
    $hash = hashPassword($password);
    $email = ($password == 'admin') ? 'admin@schule.de' :
        (($password == 'schueler123') ? 'schueler@schule.de' : 'lehrer@schule.de');
    $rolle = ($password == 'admin') ? "'admin'" :
        (($password == 'schueler123') ? "'schüler'" : "'lehrer'");

    echo "    ('$name', '$email', '$hash', $rolle)";

    $counter++;
    if ($counter < count($passwords)) {
        echo ",";
    }
    echo "<br>";
}
?>

<?php

require_once __DIR__ . "/../config/database.php";
$database = new Database();
$conn = $database->getConnection();

function login($email, $password, $conn) {
    $email = $conn->real_escape_string($email);

    $sql = "SELECT * FROM benutzer WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (verifyPassword($password, $user['passwort_hash'])) {

            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['benutzername'];
            $_SESSION['role'] = $user['rolle'];

            return [
                'success' => true,
                'message' => 'Login erfolgreich!',
                'user' => $user
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Falsches Passwort!'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Benutzer nicht gefunden!'
        ];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = login($email, $password, $conn);

    if ($result['success']) {
        echo "Willkommen, " . $_SESSION['username'] . "!";


    } else {
        echo "Fehler: " . $result['message'];
    }
}
?>


<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Passwort" required>
    <button type="submit">Login</button>
</form>
