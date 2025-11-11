<?php
$faecher = [
    "Mathematik", "Deutsch", "Englisch",
    "Geschichte", "Biologie", "Musik"
];

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Lernwebseite</title>
</head>
<body>
    <ul>
        <?php foreach ($faecher as $fach): ?>
            <li><?php echo $fach; ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
