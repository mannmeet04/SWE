<?php
session_start();

require_once "config/database.php";
require_once "includes/password_functions.php";

$database = new Database();
$pdo = $database->getConnection();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $passwort = trim($_POST['passwort']);

    if (empty($email) || empty($passwort)) {
        $error = "Bitte E-Mail und Passwort eingeben.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM benutzer WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && verifyPassword($passwort, $user['passwort_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['benutzername'] = $user['benutzername'];
            $_SESSION['rolle'] = $user['rolle'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['angemeldet'] = true;

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "E-Mail oder Passwort falsch.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lernwebseite</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body id="log-body">
<div id="gesamt-log">
    <h1 id="log-h1">Anmeldung</h1>

    <?php if (!empty($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 10px; text-align: center;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form class="form" action="login.php" method="POST">
        <div class="email">
            <label class="email-input">
                E-Mail:
            </label>
            <input type="email" class="email-input" name="email" required>
        </div>

        <div id="passwort">
            <label class="pass-input">
                Passwort:
            </label>
            <input type="password" class="pass-input" name="passwort" required>
        </div>

        <div class="button-group vertical">
            <button type="submit" class="log-ht">
                Login
            </button>

            <button type="button" class="log-ht back-btn" onclick="window.location.href='dashboard.php'">
                Als Gast weiter
            </button>
        </div>

        
    </form>
</div>

<?php include 'includes/accessibility.php'; ?>
<script src="js/accessibility.js"></script>
</body>
</html>