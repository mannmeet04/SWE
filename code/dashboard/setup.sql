-- Datenbank zurücksetzen
DROP DATABASE IF EXISTS lernwebseite;

CREATE DATABASE lernwebseite;
USE lernwebseite;

-- 1. Tabelle: Hauptfächer (Dashboard Ebene)
CREATE TABLE hauptfaecher (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              name VARCHAR(100) NOT NULL,
                              bild VARCHAR(255),
                              icon VARCHAR(50),
                              sort_order INT DEFAULT 0,
                              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabelle: Unterthemen (Fach-Dashboard Ebene)
CREATE TABLE unterthemen (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             hauptfach_id INT NOT NULL,
                             name VARCHAR(100) NOT NULL,
                             bild VARCHAR(255),
                             icon VARCHAR(50),
                             sort_order INT DEFAULT 0,
                             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabelle: Lerninhalte (Unterthema Detail Ebene)
CREATE TABLE lerninhalte (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             unterthema_id INT NOT NULL,
                             titel VARCHAR(255) NOT NULL,
                             inhalt TEXT,
                             typ ENUM('erklaerung', 'uebung', 'video'),
                             datei_pfad VARCHAR(255),
                             video_url VARCHAR(255),
                             sort_order INT DEFAULT 0,
                             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hauptfächer einfügen
INSERT INTO hauptfaecher (name, bild, icon, sort_order) VALUES
                                                            ('Mathematik', 'img/mathe.jpg', 'fas fa-calculator', 1),
                                                            ('Deutsch', 'img/deutsch.jpg', 'fas fa-book-open', 2),
                                                            ('Englisch', 'img/englisch.jpg', 'fas fa-globe', 3),
                                                            ('Geschichte', 'img/geschichte.jpg', 'fas fa-landmark', 4),
                                                            ('Biologie', 'img/biologie.jpg', 'fas fa-leaf', 5),
                                                            ('Musik', 'img/musik.jpg', 'fas fa-music', 6);

-- Unterthemen für Mathematik (hauptfach_id = 1)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (1, 'Zahlen und Rechnen', 'img/zahlen_rechnen.jpg', 'fas fa-sort-numeric-up', 1),
                                                                         (1, 'Geometrie', 'img/geometrie.jpg', 'fas fa-ruler-combined', 2),
                                                                         (1, 'Daten und Zufall', 'img/daten_zufall.jpg', 'fas fa-chart-pie', 3);

-- Unterthemen für Deutsch (hauptfach_id = 2)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (2, 'Wortarten', 'img/wortarten.jpg', 'fas fa-pen-fancy', 1),
                                                                         (2, 'Rechtschreibung', 'img/rechtschreibung.jpg', 'fas fa-spell-check', 2),
                                                                         (2, 'Grammatik', 'img/grammatik.jpg', 'fas fa-comment-dots', 3);


-- Lerninhalte für Wortarten (unterthema_id = 4)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (4, 'Was sind Wortarten?', 'Wortarten sind Gruppen von Wörtern mit ähnlicher Funktion. Zum Beispiel:\n\n- Nomen: Dinge, Personen, Orte (Haus, Lehrer, Berlin)\n- Verben: Handlungen oder Zustände (laufen, schlafen, sein)\n- Adjektive: beschreiben Nomen (schön, laut, freundlich)\n- Artikel: Begleiter von Nomen (der, die, das, ein, eine)\n- Pronomen: Fürwörter (ich, du, er, sie, es)\n\nDiese Grundlagen helfen dir, Sätze richtig zu bilden und zu verstehen.', 'erklaerung', NULL, 1),
                                                                                       (4, 'Übungsblätter Wortarten', 'Lade dir passende Arbeitsblätter herunter und übe die verschiedenen Wortarten:', 'uebung', NULL, 2),
                                                                                       (4, 'Wortarten einfach erklärt', 'In diesem Video lernst du alle wichtigen Wortarten kennen und verstehst, wie sie im Satz zusammenwirken.', 'video', 'https://www.youtube.com/embed/1i1JPN4ulaE', 3);


-- Unterthemen für Englisch (hauptfach_id = 3)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (3, 'Vocabulary', 'img/vocab.jpg', 'fas fa-pen-fancy', 1),
                                                                         (3, 'Reading & Listening', 'img/reading.jpg', 'fas fa-spell-check', 2),
                                                                         (3, 'Grammar', 'img/grammatik.jpg', 'fas fa-comment-dots', 3);


-- Lerninhalte für Vocabulary (unterthema_id = 7)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (7, ' Numbers and colors:', 'Numbers and Colors are important words we use every day. For example:\n\n- Numbers: tell how many or the order (one, two, three, ten)\n- Colors: show how something looks (red, blue, green, yellow)\n\n These words help you count things and describe objects.', 'erklaerung', NULL, 1),
                                                                                       (7, 'Colors & Numbers Exercises', 'Download Exercises: Learn and Practice Numbers and Colors:', 'uebung', NULL, 2),
                                                                                       (7, 'Watch and learn: Colors & Numbers  ', 'In this video, you will learn colors and numbers. Watch, listen, and repeat the words. This will help you remember them!', 'video', 'https://www.youtube.com/embed/UwmmULz9-Mk', 3);



-- Lerninhalte für Reading & Listening (unterthema_id = 8)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (8, ' My Family and My Day:', ' In this topic, you will learn the names of family members and words about daily activities. You will read short texts and listen to simple dialogues to practice understanding and speaking English.', 'erklaerung', NULL, 1),
                                                                                       (8, 'Exercises:', 'Download Exercises: Learn About Family and Daily Activities:', 'uebung', NULL, 2),
                                                                                       (8, 'Watch and learn:  ', 'In this video, you will learn words about family and daily activities. Watch, listen, and repeat the sentences. This will help you understand and remember them!', 'video', 'https://www.youtube.com/embed/FHaObkHEkHQ', 3);



-- Lerninhalte für Grammar (unterthema_id = 9)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (9, ' Personal Pronouns & the Verb "to be":', 'Personal pronouns and the verb “to be” help you make simple sentences. For example:\n\n - Personal pronouns: I, you, he, she, it, we, they\n- Verb “to be”: am, are, is\n These words help you talk about yourself and other people in easy sentences.', 'erklaerung', NULL, 1),
                                                                                       (9, 'Exercises: ', 'Download Exercises: Practice Personal Pronouns & the Verb "to be" ', 'uebung', NULL, 2),
                                                                                       (9, 'Watch and learn: ', 'In this video, you will learn personal pronouns and the verb “to be”. Watch, listen, and repeat the sentences.', 'video', 'https://www.youtube.com/embed/7UC4RQhGo54', 3);

INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (5, 'Der menschliche Körper', 'img/menschlicherkorper.png', 'fas fa-pen-fancy', 1),
                                                                         (5, 'Pflanzen', 'img/pflanzen.jpg', 'fas fa-spell-check', 2),
                                                                         (5, 'Tiere', 'img/tiere2.jpg', 'fas fa-comment-dots', 3);


INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (10, 'Der menschliche Körper – einfach erklärt', 'Der menschliche Körper besteht aus vielen Organen, die zusammenarbeiten.
                                                                                     Wichtige Organe:
                                                                                         - Herz: pumpt Blut durch den Körper
                                                                                         - Lunge: sorgt für Atmung
                                                                                         - Gehirn: steuert den ganzen Körper
                                                                                         - Magen und Darm: helfen beim Verdauen
                                                                                         - Knochen und Muskeln: geben dem Körper Form und Kraft',
                                                                                        'erklaerung', NULL, 1),
                                                                                       (10, 'Übungsblätter menschliche körper', 'Lade dir passende Arbeitsblätter herunter und übe die verschiedenen Wortarten:', 'uebung', NULL, 2),
                                                                                       (10, 'menschliche körper einfach erklaärt', 'In diesem Video lernst du die wichtigsten Teile des menschlichen Körpers kennen und verstehst, wie sie zusammenarbeiten.', 'video', 'https://www.youtube.com/embed/M3W9mfJ-ER4
', 3);
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (11, 'Pflanzen – einfach erklärt',
                                                                                        'Pflanzen sind lebende Organismen, die überall auf der Erde zu finden sind. Sie wachsen in Wäldern, Wüsten, Gärten, Parks und sogar in deiner Wohnung. Pflanzen sind sehr wichtig für die Erde, weil sie Sauerstoff produzieren, den Menschen und Tiere zum Atmen brauchen.

                                                                                                  Eine typische Pflanze besteht aus vier Hauptteilen:

                                                                                              Wurzel: Sie verankert die Pflanze im Boden und nimmt Wasser und Mineralien auf.

                                                                                              Stängel: Er trägt die Blätter und bringt Wasser und Nährstoffe im ganzen Körper der Pflanze nach oben.

                                                                                              Blätter: Hier passiert die Fotosynthese – die Pflanze stellt aus Sonnenlicht, Wasser und Luft ihre eigene Nahrung her.

                                                                                              Blüte: Viele Pflanzen bilden Blüten, um sich fortzupflanzen und Samen zu bilden.

                                                                                              Pflanzen sind einzigartig, weil sie selbst Nahrung herstellen können. Ohne Pflanzen gäbe es keine Tiere und keine Menschen – sie starten jede Nahrungskette und halten das Gleichgewicht der Natur aufrecht.Pflanzen bestehen aus Wurzel, Stängel, Blättern und Blüten. Sie nehmen Wasser über die Wurzeln auf, machen mit Licht Energie (Fotosynthese) und wachsen so.',
                                                                                        'erklaerung', NULL, 1),

                                                                                       (11, 'Übungsblätter Pflanzen',
                                                                                        'Lade dir passende Arbeitsblätter herunter und übe den Aufbau einer Pflanze und die Funktionen der einzelnen Teile.',
                                                                                        'uebung', NULL, 2),

                                                                                       (11, 'Pflanzen einfach erklärt – Video',
                                                                                        'In diesem Video lernst du, wie Pflanzen aufgebaut sind und wie Fotosynthese funktioniert.',

                                                                                        'video', 'https://www.youtube.com/embed/6zXl9Ym8EPI', 3);


INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (12, 'Tiere – einfach erklärt',
                                                                                        'Tiere gehören zu verschiedenen Gruppen wie Säugetiere, Vögel, Fische, Insekten und Reptilien.
                                                                                        Jede Tierart hat besondere Merkmale, die ihr helfen, in ihrem Lebensraum zu überleben.
                                                                                        Manche Tiere leben im Wasser, andere an Land oder in der Luft.
                                                                                        Viele Tiere haben spezielle Anpassungen wie Tarnung oder schnelle Bewegung.
                                                                                        So hat jede Tierart ihren eigenen Platz in der Natur.',
                                                                                        'erklaerung', NULL, 1),

                                                                                       (12, 'Übungsblätter Tiere',
                                                                                        'Lade dir passende Arbeitsblätter herunter und übe die Tiergruppen und Lebensräume.',
                                                                                        'uebung', NULL, 2),

                                                                                       (12, 'Tiere einfach erklärt – Video',
                                                                                        'In diesem Video lernst du die wichtigsten Tiergruppen und ihre Lebensräume kennen.',
                                                                                        'video', 'https://www.youtube.com/embed/c56s0ezc8sg', 3);

UPDATE lerninhalte
SET datei_pfad = 'pdf_dateien/Nachhilfematerial_fuer_fuenfte_Klasse_first_five.pdf'
WHERE id = 2;