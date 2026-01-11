<?php
$isLoggedIn = isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
$username = $_SESSION['benutzername'] ?? '';
$userRole = $_SESSION['rolle'] ?? 'guest';
$isAdmin = $userRole === 'admin';
$isTeacher = $userRole === 'lehrer';
$isStudent = $userRole === 'schüler';
if (!isset($base_path)) {
    $current_path = $_SERVER['PHP_SELF'] ?? '';
    $base_path = '';
    if (strpos($current_path, 'pages/admin') !== false || strpos($current_path, 'pages\\admin') !== false) {
        $base_path = '../../';
    } elseif (strpos($current_path, 'pages/') !== false || strpos($current_path, 'pages\\') !== false) {
        $base_path = '../';
    } else {
        $base_path = '';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lernwebseite - <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="<?= $base_path ?>style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php
    if (!empty($extraHead)) {
        echo $extraHead;
    }
    ?>
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="<?= $base_path ?>img/logo.png" alt="Logo">
    </button>
    <span class="page-title"><?= htmlspecialchars($pageTitle ?? 'Mein Dashboard') ?></span>

    <div class="search-container">
        <form action="<?= $base_path ?>pages/search.php" method="GET" class="search-form">
            <input type="text" name="query" id="search-input" placeholder="Suchen..." required autocomplete="off">
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
                            'schüler' => 'Schüler'
                        ];
                        echo $roleBadge[$userRole] ?? 'Gast';
                        ?>
                    </span>
            </div>
            <a href="<?= $base_path ?>logout.php" id="logout-btn" class="auth-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="<?= $base_path ?>login.php" id="login-btn" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        <?php endif; ?>
    </div>
</header>