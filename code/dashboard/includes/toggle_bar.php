<?php
$unterthema_id = $unterthema_id ?? 1;
$active_tab = $active_tab ?? 'erklaerung';
?>

<div class="toggle-bar">
    <a href="?unterthema_id=<?= $unterthema_id ?>&tab=erklaerung" class="toggle-option <?= $active_tab == 'erklaerung' ? 'active' : '' ?>">
        <i class="fa-solid fa-book-open"></i> Erklärung
    </a>
    <a href="?unterthema_id=<?= $unterthema_id ?>&tab=uebung" class="toggle-option <?= $active_tab == 'uebung' ? 'active' : '' ?>">
        <i class="fa-solid fa-pen-to-square"></i> Übungen
    </a>
    <a href="?unterthema_id=<?= $unterthema_id ?>&tab=video" class="toggle-option <?= $active_tab == 'video' ? 'active' : '' ?>">
        <i class="fa-solid fa-play"></i> Videos
    </a>
</div>