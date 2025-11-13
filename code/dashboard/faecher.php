<?php


$ut_mathe = [
    [
        "name" => "Zahlen und Rechnen",
        "bild" => "img/zahlen_rechnen.jpg",
        "link" => "../faecher_dashboards/zahlen_und_rechnen.php",
        "icon" => "fas fa-sort-numeric-up"
    ],
    [
        "name" => "Geometrie",
        "bild" => "img/geometrie.jpg",
        "link" => "../faecher_dashboards/geometrie.php",
        "icon" => "fas fa-ruler-combined"
    ],
    [
        "name" => "Daten und Zufall",
        "bild" => "img/daten_zufall.jpg",
        "link" => "../faecher_dashboards/daten_und_zufall.php",
        "icon" => "fas fa-chart-pie"
    ],
    [
        "name" => "Größen und Messen",
        "bild" => "img/groessen_messen.jpg",
        "link" => "../faecher_dashboards/groessen_und_messen.php",
        "icon" => "fas fa-weight-hanging"
    ],
    [
        "name" => "Denken und Problemlösen",
        "bild" => "img/denken_problemloesen.jpg",
        "link" => "../faecher_dashboards/denken_und_problemloesen.php",
        "icon" => "fas fa-lightbulb"
    ],
    [
        "name" => "Platzhalter",
        "bild" => "img/musik.jpg",
        "link" => "../faecher_dashboards/platzhalter.php",
        "icon" => "fas fa-bookmark"
    ]
];

$ut_deutsch = [
    [
        'name' => 'Wortarten',
        'bild' => 'img/wortarten.jpg',
        'link' => '../faecher_dashboards/deutsch_wortarten.php',
        'icon' => 'fas fa-pen-fancy'
    ],
    [
        'name' => 'Rechtschreibung',
        'bild' => 'img/rechtschreibung.jpg',
        'link' => 'themen/rechtschreibung.php',
        'icon' => 'fas fa-spell-check'
    ],
    [
        'name' => 'Grammatik',
        'bild' => 'img/grammatik.jpg',
        'link' => 'themen/grammatik.php',
        'icon' => 'fas fa-comment-dots'
    ],
    [
        'name' => 'Lesen & Texte',
        'bild' => 'img/lesen.jpg',
        'link' => 'themen/lesen.php',
        'icon' => 'fas fa-glasses'
    ],
    [
        'name' => 'Aufsatz schreiben',
        'bild' => 'img/aufsatz.jpg',
        'link' => 'themen/aufsatz.php',
        'icon' => 'fas fa-scroll'
    ],
    [
        'name' => 'Zeichensetzung',
        'bild' => 'img/zeichensetzung.jpg',
        'link' => 'themen/zeichensetzung.php',
        'icon' => 'fas fa-edit'
    ]
];


$faecher = [
    [
        "name" => "Mathematik",
        "bild" => "img/mathe.jpg",
        "link" => "#",
        "icon" => "fas fa-calculator",

        "subfaecher" => $ut_mathe
    ],
    [
        "name" => "Deutsch",
        "bild" => "img/deutsch.jpg",
        "link" => "../faecher_dashboards/deutsch_dashboard/deutsch_dashboard.php",
        "icon" => "fas fa-book-open",

        "subfaecher" => $ut_deutsch
    ],
    [
        "name" => "Englisch",
        "bild" => "img/englisch.jpg",
        "link" => "../faecher_dashboards/englisch_dashboard.php",
        "icon" => "fas fa-globe",
        "subfaecher" => []
    ],
    [
        "name" => "Geschichte",
        "bild" => "img/geschichte.jpg",
        "link" => "../faecher_dashboards/geschichte_dashboard.php",
        "icon" => "fas fa-landmark",
        "subfaecher" => []
    ],
    [
        "name" => "Biologie",
        "bild" => "img/biologie.jpg",
        "link" => "../faecher_dashboards/biologie_dashboard.php",
        "icon" => "fas fa-leaf",
        "subfaecher" => []
    ],
    [
        "name" => "Musik",
        "bild" => "img/musik.jpg",
        "link" => "../faecher_dashboards/musik_dashboard.php",
        "icon" => "fas fa-music",
        "subfaecher" => []
    ]
];

?>