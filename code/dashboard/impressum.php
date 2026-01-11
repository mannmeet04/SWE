<?php
// impressum.php - Impressum Seite
session_start();

if (!isset($_SESSION['angemeldet'])) {
    $_SESSION['angemeldet'] = false;
    $_SESSION['rolle'] = 'guest';
    $_SESSION['benutzername'] = 'Gast';
}

$pageTitle = 'Impressum';
$isLoggedIn = $_SESSION['angemeldet'];
$username = $_SESSION['benutzername'] ?? '';
$userRole = $_SESSION['rolle'] ?? 'guest';
$isAdmin = $userRole === 'admin';
$isTeacher = $userRole === 'lehrer';
$isStudent = $userRole === 'schüler';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lernwebseite - <?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .impressum-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .impressum-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 2.5em;
        }

        .impressum-section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: white;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .impressum-section h2 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .address-block {
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .address-block p {
            margin: 5px 0;
            color: #555;
            font-size: 1.1em;
        }

        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px;
            background-color: #f0f8ff;
            border-radius: 4px;
        }

        .contact-icon {
            color: #007bff;
            font-size: 1.5em;
            margin-right: 20px;
            min-width: 30px;
            text-align: center;
        }

        .contact-link {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }

        .contact-link:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .contact-text {
            color: #555;
        }

        /* ==================== DARK MODE FOR IMPRESSUM ==================== */
        body.dark-mode .impressum-container {
            background-color: #2a2a2a;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .impressum-section {
            background-color: #1a1a1a;
            border-left-color: #66b3ff;
        }

        body.dark-mode .impressum-section h2 {
            color: #66b3ff;
        }

        body.dark-mode .address-block p {
            color: #e0e0e0;
        }

        body.dark-mode .contact-info {
            background-color: #2a2a2a;
            border: 1px solid #333;
        }

        body.dark-mode .contact-icon {
            color: #66b3ff;
        }

        body.dark-mode .contact-link {
            color: #66b3ff;
        }

        body.dark-mode .contact-link:hover {
            color: #99ccff;
        }
    </style>
</head>
<body>
<header>
    <button class="menu-btn logo-btn" onclick="toggleSidebar()">
        <img src="img/logo.png" alt="Logo">
    </button>
    <span class="page-title">Impressum</span>

    <div class="search-container">
        <form action="pages/search.php" method="GET" class="search-form">
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
                            'schüler' => 'Schüler',
                            'guest' => 'Gast'
                    ];
                    echo $roleBadge[$userRole] ?? 'Gast';
                    ?>
                </span>
            </div>
            <a href="logout.php" id="logout-btn" class="auth-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" id="login-btn" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        <?php endif; ?>
    </div>
</header>

<?php include 'includes/sidebar.php'; ?>

<main>
    <div class="content">
        <div class="impressum-container">
            <h1><i class="fas fa-file-contract"></i> Impressum</h1>

            <!-- Horst-Schlämmer-Gedächtnis-Gymnasium Section -->
            <div class="impressum-section">
                <h2><i class="fas fa-school"></i> Horst-Schlämmer-Gedächtnis-Gymnasium</h2>
                <div class="address-block">
                    <p>Schätzeleinstraße 31</p>
                    <p>41515 Grevenbroich</p>
                    <p>Deutschland</p>
                </div>
            </div>

            <!-- Projektteam Section -->
            <div class="impressum-section">
                <h2><i class="fas fa-users"></i> Projektteam SWE FH Aachen</h2>
                <div class="address-block">
                    <p>Eupener Straße 70</p>
                    <p>52066 Aachen</p>
                    <p>Deutschland</p>
                </div>
            </div>

            <!-- Kontaktinformationen -->
            <div class="impressum-section">
                <h2><i class="fas fa-envelope"></i> Kontaktinformationen</h2>

                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <a href="mailto:kontakt@hsg-grevenbroich.de" class="contact-link">kontakt@hsg-grevenbroich.de</a>
                    </div>
                </div>

                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <a href="tel:+492181123456" class="contact-link">+49 (0) 2181 / 123 456</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<footer>
    <div id="imp"><a href="impressum.php" style="color: inherit; text-decoration: none;">Impressum</a></div>
</footer>

<?php include 'includes/accessibility.php'; ?>
<script src="js/accessibility.js"></script>
<script src="js/search-mobile.js"></script>
<script src="js/header-responsive.js"></script>
</body>
</html>

