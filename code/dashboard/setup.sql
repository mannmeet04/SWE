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
-- Lerninhalte für Zahlen und Rechnen (unterthema_id = 1)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (1, 'Zahlen und Rechnen', 'Dieses Unterthema bildet die Grundlage der Mathematik und befasst sich mit den Zahlenmengen (wie natürliche, ganze, rationale und reelle Zahlen) sowie den Grundrechenarten wie Addition, Subtraktion, Multiplikation und Division. Man lernt, Zahlen darzustellen, ihren Wert zu verstehen und sie miteinander zu verknüpfen. Ein Beispiel ist das Lösen einfacher Gleichungen wie $2x + 5 = 11$ oder die Berechnung des Produkts $3 * 4 = 12$.
', 'erklaerung', NULL, 1),
                                                                                       (1, 'Übungsblätter Zahlen und Rechnen', 'Lade dir passende Arbeitsblätter herunter und übe das Rechnen', 'uebung', NULL, 2),
                                                                                       (1, 'Zahlen und Rechnen einfach erklärt', 'In diesem Video lernst du Zahlen und Rechnen.', 'video', 'https://www.youtube.com/embed/b2KcUWxG_wI', 3);

-- Lerninhalte für Geometrie (unterthema_id = 2)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (2, 'Geometrie', 'Die Geometrie beschäftigt sich mit Formen, Größen, relativen Positionen von Objekten und den Eigenschaften des Raumes. Sie umfasst die Untersuchung von Punkten, Linien, Flächen und Körpern. Typische Aufgaben sind die Berechnung des Umfangs oder der Fläche eines Kreises ($A = \\pi r^2$) oder die Bestimmung des Volumens eines Würfels.', 'erklaerung', NULL, 1),
                                                                                       (2, 'Übungsblätter Geometrie', 'Lade dir passende Arbeitsblätter herunter und übe die Geometrie:', 'uebung', NULL, 2),
                                                                                       (2, 'Geometrie einfach erklärt', 'In diesem Video lernst du wie man den Flächeninhalt und den Umfang eines Kreises Berechnet', 'video', 'https://www.youtube.com/embed/eASZX0ocAc8', 3);

-- Lerninhalte für Daten und Zufall (unterthema_id = 3)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (3, 'Daten und Zufall', 'Dieser Bereich, oft als Stochastik bezeichnet, befasst sich mit der Erfassung, Organisation und Analyse von Daten (Statistik) sowie der Wahrscheinlichkeit von Ereignissen. Man lernt, Häufigkeiten darzustellen (z.B. in Diagrammen) und Vorhersagen über zufällige Vorgänge zu treffen. Ein einfaches Beispiel ist das Berechnen der Wahrscheinlichkeit, beim einmaligen Würfeln eine Sechs zu erhalten (was $\frac{1}{6}$ beträgt).', 'erklaerung', NULL, 1),
                                                                                       (3, 'Übungsblätter Daten und Zufall', 'Lade dir passende Arbeitsblätter herunter und übe Statistik:', 'uebung', NULL, 2),
                                                                                       (3, 'Daten und Zufall einfach erklärt', 'In diesem Video lernst du alle wichtigen Grundlagen der Statistik', 'video', 'https://www.youtube.com/embed/XQIU-4G9-4s', 3);

-- Unterthemen für Deutsch (hauptfach_id = 2)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (2, 'Wortarten', 'img/wortarten.png', 'fas fa-pen-fancy', 1),
                                                                         (2, 'Rechtschreibung', 'img/rechtschreibung.png', 'fas fa-spell-check', 2),
                                                                         (2, 'Grammatik', 'img/grammatik.jpg', 'fas fa-comment-dots', 3);


-- Lerninhalte für Wortarten (unterthema_id = 4)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (4, 'Was sind Wortarten?', 'Wortarten sind Gruppen von Wörtern mit ähnlicher Funktion. Zum Beispiel:\n\n- Nomen: Dinge, Personen, Orte (Haus, Lehrer, Berlin)\n- Verben: Handlungen oder Zustände (laufen, schlafen, sein)\n- Adjektive: beschreiben Nomen (schön, laut, freundlich)\n- Artikel: Begleiter von Nomen (der, die, das, ein, eine)\n- Pronomen: Fürwörter (ich, du, er, sie, es)\n\nDiese Grundlagen helfen dir, Sätze richtig zu bilden und zu verstehen.', 'erklaerung', NULL, 1),
                                                                                       (4, 'Übungsblätter Wortarten', 'Lade dir passende Arbeitsblätter herunter und übe die verschiedenen Wortarten:', 'uebung', NULL, 2),
                                                                                       (4, 'Wortarten einfach erklärt', 'In diesem Video lernst du alle wichtigen Wortarten kennen und verstehst, wie sie im Satz zusammenwirken.', 'video', 'https://www.youtube.com/embed/1i1JPN4ulaE', 3);

-- Lerninhalte für Rechtschreibung (unterthema_id = 5)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (5, 'Rechtschreibung', 'Rechtschreibung (Orthografie) umfasst die festgelegten Regeln für die korrekte Schreibung von Wörtern in einer Sprache. Dazu gehören die Groß- und Kleinschreibung, die korrekte Verwendung von Dehnungen ($ie$, $h$), Schärfungen ($ss$, $ß$) und die Schreibung von Fremdwörtern. Ein häufiges Beispiel ist die Unterscheidung, ob man "das" (Artikel/Pronomen) oder "dass" (Konjunktion) schreiben muss.', 'erklaerung', NULL, 1),
                                                                                       (5, 'Übungsblätter Rechtschreibung', 'Lade dir passende Arbeitsblätter herunter und übe die Rechtschreibung:', 'uebung', NULL, 2),
                                                                                       (5, 'Rechtschreibung einfach erklärt', 'In diesem Video lernst du alle wichtigen Rechtschreibregeln.', 'video', 'https://www.youtube.com/embed/ECazrcAfNHw', 3);

-- Lerninhalte für Grammatik (unterthema_id = 6)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (6, 'Grammatik', 'Grammatik befasst sich mit den Regeln für den Aufbau und die Struktur der Sprache, insbesondere auf Satzebene. Sie umfasst die Lehre vom Satzbau (Syntax), den Formen der Wörter (Morphologie wie Konjugation und Deklination) und der korrekten Verwendung von Tempus und Modus. Ein Beispiel ist die Deklination des Nomens "die Frau" im Genitiv Singular zu "der Frau" oder die Konjugation des Verbs "laufen" in der 1. Person Singular Präsens zu "ich laufe".', 'erklaerung', NULL, 1),
                                                                                       (6, 'Übungsblätter Grammatik', 'Lade dir passende Arbeitsblätter herunter und übe die Grammatik:', 'uebung', NULL, 2),
                                                                                       (6, 'Grammatik einfach erklärt', 'In diesem Video lernst du alles wichtige über Grammatik.', 'video', 'https://www.youtube.com/embed/v9nZBRUJ7Pk', 3);

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

-- Unterthemen für Geschichte (hauptfach_id = 4)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (4, 'Steinzeit', 'img/steinzeit.png', 'fas fa-pen-fancy', 1),
                                                                         (4, 'Antikes Griechenland', 'img/antikes_griechenland.png', 'fas fa-spell-check', 2),
                                                                         (4, 'Antikes Rom', 'img/romer.png', 'fas fa-comment-dots', 3);

-- Lerninhalte für Steinzeit (unterthema_id = 10)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (10, 'Steinzeit', 'Die Steinzeit ist die älteste und längste Epoche der Menschheitsgeschichte, in der Stein das primäre Material für die Herstellung von Werkzeugen war. Sie wird in Altsteinzeit (Jäger und Sammler), Mittelsteinzeit und Jungsteinzeit (Beginn von Ackerbau und Viehzucht) unterteilt. Wichtige Entwicklungen waren die Beherrschung des Feuers, die Entwicklung von Sprache und die Entstehung von Höhlenmalereien.', 'erklaerung', NULL, 1),
                                                                                       (10, 'Übungsblätter Steinzeit', 'Lade dir passende Arbeitsblätter herunter und lerne mehr über die Steinzeit ', 'uebung', NULL, 2),
                                                                                       (10, 'Steinzeit einfach erklärt', 'In diesem Video lernst du mehr Über die Steinzeit', 'video', 'https://www.youtube.com/embed/4LLH6TC0mjg', 3);

-- Lerninhalte für Antikes Griechenland (unterthema_id = 11)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (11, 'Antikes Griechenland', 'Das Antike Griechenland (ca. 800 v. Chr. bis 146 v. Chr.) war die Wiege der westlichen Zivilisation und setzte sich aus vielen unabhängigen Stadtstaaten (Polis) wie Athen und Sparta zusammen. Es ist bekannt für die Entwicklung der Demokratie, die Olympischen Spiele sowie bahnbrechende Leistungen in Philosophie (Sokrates, Platon), Wissenschaft und Architektur (Parthenon).', 'erklaerung', NULL, 1),
                                                                                       (11, 'Übungsblätter Antikes Griechenland', 'Lade dir passende Arbeitsblätter herunter und lerne mehr über das Antikes Griechenland', 'uebung', NULL, 2),
                                                                                       (11, 'Antikes Griechenland einfach erklärt', 'In diesem Video lernst du mehr Über das Antike Griechenland', 'video', 'https://www.youtube.com/embed/QLOpPK3_Y2U', 3);

-- Lerninhalte für Antikes Rom (unterthema_id = 12)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (12, 'Antikes Rom', 'Das Antike Rom entwickelte sich von einem kleinen Stadtstaat (ab 753 v. Chr.) zu einem riesigen Reich, das weite Teile Europas, Nordafrikas und des Nahen Ostens umfasste. Es prägte die Rechtsgrundsätze, das Ingenieurwesen (Aquädukte, Straßen) und die lateinische Sprache, aus der sich die romanischen Sprachen entwickelten. Schlüsselereignisse sind die Gründung der Römischen Republik und später des Römischen Kaiserreichs unter Kaisern wie Augustus.', 'erklaerung', NULL, 1),
                                                                                       (12, 'Übungsblätter Antikes Rom', 'Lade dir passende Arbeitsblätter herunter und lerne mehr über das Antike Rom ', 'uebung', NULL, 2),
                                                                                       (12, 'Antikes Rom einfach erklärt', 'In diesem Video lernst du mehr Über das Antikes Rom', 'video', 'https://www.youtube.com/embed/O7LH4JmmRV8', 3);
-- Unterthemen für Biologie (hauptfach_id = 5)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (5, 'Der menschliche Körper', 'img/menschlicherkorper.png', 'fas fa-pen-fancy', 1),
                                                                         (5, 'Pflanzen', 'img/pflanzen.jpg', 'fas fa-spell-check', 2),
                                                                         (5, 'Tiere', 'img/tiere2.jpg', 'fas fa-comment-dots', 3);


INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (13, 'Der menschliche Körper – einfach erklärt', 'Der menschliche Körper besteht aus vielen Organen, die zusammenarbeiten.
                                                                                     Wichtige Organe:
                                                                                         - Herz: pumpt Blut durch den Körper
                                                                                         - Lunge: sorgt für Atmung
                                                                                         - Gehirn: steuert den ganzen Körper
                                                                                         - Magen und Darm: helfen beim Verdauen
                                                                                         - Knochen und Muskeln: geben dem Körper Form und Kraft',
                                                                                        'erklaerung', NULL, 1),
                                                                                       (13, 'Übungsblätter menschliche körper', 'Lade dir passende Arbeitsblätter herunter und übe die verschiedenen Wortarten:', 'uebung', NULL, 2),
                                                                                       (13, 'menschliche körper einfach erklaärt', 'In diesem Video lernst du die wichtigsten Teile des menschlichen Körpers kennen und verstehst, wie sie zusammenarbeiten.', 'video', 'https://www.youtube.com/embed/M3W9mfJ-ER4
', 3);

-- Lerninhalte für Pflanzen (unterthema_id = 14)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (14, 'Pflanzen – einfach erklärt',
                                                                                        'Pflanzen sind lebende Organismen, die überall auf der Erde zu finden sind. Sie wachsen in Wäldern, Wüsten, Gärten, Parks und sogar in deiner Wohnung. Pflanzen sind sehr wichtig für die Erde, weil sie Sauerstoff produzieren, den Menschen und Tiere zum Atmen brauchen.

                                                                                                  Eine typische Pflanze besteht aus vier Hauptteilen:

                                                                                              Wurzel: Sie verankert die Pflanze im Boden und nimmt Wasser und Mineralien auf.

                                                                                              Stängel: Er trägt die Blätter und bringt Wasser und Nährstoffe im ganzen Körper der Pflanze nach oben.

                                                                                              Blätter: Hier passiert die Fotosynthese – die Pflanze stellt aus Sonnenlicht, Wasser und Luft ihre eigene Nahrung her.

                                                                                              Blüte: Viele Pflanzen bilden Blüten, um sich fortzupflanzen und Samen zu bilden.

                                                                                              Pflanzen sind einzigartig, weil sie selbst Nahrung herstellen können. Ohne Pflanzen gäbe es keine Tiere und keine Menschen – sie starten jede Nahrungskette und halten das Gleichgewicht der Natur aufrecht.Pflanzen bestehen aus Wurzel, Stängel, Blättern und Blüten. Sie nehmen Wasser über die Wurzeln auf, machen mit Licht Energie (Fotosynthese) und wachsen so.',
                                                                                        'erklaerung', NULL, 1),

                                                                                       (14, 'Übungsblätter Pflanzen',
                                                                                        'Lade dir passende Arbeitsblätter herunter und übe den Aufbau einer Pflanze und die Funktionen der einzelnen Teile.',
                                                                                        'uebung', NULL, 2),

                                                                                       (14, 'Pflanzen einfach erklärt – Video',
                                                                                        'In diesem Video lernst du, wie Pflanzen aufgebaut sind und wie Fotosynthese funktioniert.',

                                                                                        'video', 'https://www.youtube.com/embed/6zXl9Ym8EPI', 3);


INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (15, 'Tiere – einfach erklärt',
                                                                                        'Tiere gehören zu verschiedenen Gruppen wie Säugetiere, Vögel, Fische, Insekten und Reptilien.
                                                                                        Jede Tierart hat besondere Merkmale, die ihr helfen, in ihrem Lebensraum zu überleben.
                                                                                        Manche Tiere leben im Wasser, andere an Land oder in der Luft.
                                                                                        Viele Tiere haben spezielle Anpassungen wie Tarnung oder schnelle Bewegung.
                                                                                        So hat jede Tierart ihren eigenen Platz in der Natur.',
                                                                                        'erklaerung', NULL, 1),

                                                                                       (15, 'Übungsblätter Tiere',
                                                                                        'Lade dir passende Arbeitsblätter herunter und übe die Tiergruppen und Lebensräume.',
                                                                                        'uebung', NULL, 2),

                                                                                       (15, 'Tiere einfach erklärt – Video',
                                                                                        'In diesem Video lernst du die wichtigsten Tiergruppen und ihre Lebensräume kennen.',
                                                                                        'video', 'https://www.youtube.com/embed/c56s0ezc8sg', 3);

-- Unterthemen für Musik (hauptfach_id = 5)
INSERT INTO unterthemen (hauptfach_id, name, bild, icon, sort_order) VALUES
                                                                         (6, 'Notenlehre', 'img/notenlehre.png', 'fas fa-pen-fancy', 1),
                                                                         (6, 'Instrumentenlehre', 'img/instrumentenlehre.png', 'fas fa-spell-check', 2),
                                                                         (6, 'Musikgeschichte', 'img/musikgeschichte.png', 'fas fa-comment-dots', 3);

-- Lerninhalte für Notenlehre (unterthema_id = 16)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (16, 'Notenlehre', 'Die Notenlehre vermittelt die Grundlagen der musikalischen Schrift und Struktur, um Musik lesen, schreiben und verstehen zu können. Sie erklärt, wie Tonhöhen durch Notenlinien und Notenschlüssel (z.B. Violinschlüssel) und die Tondauer durch Notenwerte (Ganze, Halbe, Viertel) dargestellt werden. Ein wichtiges Element ist das Verständnis von Takten und Rhythmus.', 'erklaerung', NULL, 1),
                                                                                       (16, 'Übungsblätter Notenlehre', 'Lade dir passende Arbeitsblätter herunter und lerne mehr zu den verschiedenen Noten', 'uebung', NULL, 2),
                                                                                       (16, 'Notenlehre einfach erklärt', 'In diesem Video lernst du mehr zu Noten', 'video', 'https://www.youtube.com/embed/D_QbSLiWRQc', 3);

-- Lerninhalte für Instrumentenlehre (unterthema_id = 17)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (17, 'Instrumentenlehre', 'Dieses Unterthema befasst sich mit der Klassifizierung, dem Aufbau und der Funktionsweise verschiedener Musikinstrumente. Instrumente werden üblicherweise in Hauptgruppen wie Saiteninstrumente (Gitarre), Blasinstrumente (Trompete), Tasteninstrumente (Klavier) und Schlaginstrumente (Schlagzeug) unterteilt. Man lernt, wie die Instrumente Töne erzeugen und welche Rolle sie im Orchester oder Ensemble spielen.', 'erklaerung', NULL, 1),
                                                                                       (17, 'Übungsblätter Instrumentenlehre', 'Lade dir passende Arbeitsblätter herunter und lerne mehr zu Instrumenten', 'uebung', NULL, 2),
                                                                                       (17, 'Instrumentenlehre einfach erklärt', 'In diesem Video lernst mehr über verschiedene Instrumente', 'video', 'https://www.youtube.com/embed/qOvPWvJaMuc', 3);

-- Lerninhalte für Musikgeschichte (unterthema_id = 18)
INSERT INTO lerninhalte (unterthema_id, titel, inhalt, typ, video_url, sort_order) VALUES
                                                                                       (18, 'Musikgeschichte', 'Die Musikgeschichte verfolgt die Entwicklung und Veränderung musikalischer Stile, Formen und Kompositionstechniken im Laufe der Zeit und in verschiedenen Kulturen. Sie reicht von der Alten Musik über Epochen wie Barock (Bach) und Klassik (Mozart) bis hin zur Moderne und aktuellen populären Musik. Man untersucht, wie gesellschaftliche und kulturelle Einflüsse die Musik einer bestimmten Zeit prägten.', 'erklaerung', NULL, 1),
                                                                                       (18, 'Übungsblätter Musikgeschichte', 'Lade dir passende Arbeitsblätter herunter und übe Musikgeschichte', 'uebung', NULL, 2),
                                                                                       (18, 'Musikgeschichte einfach erklärt', 'In diesem Video lernst du mehr ur Musikgeschichte', 'video', 'https://www.youtube.com/embed/ziUEnwIKehs', 3);

UPDATE lerninhalte
SET datei_pfad = 'uploads/Nachhilfematerial_fuer_fuenfte_Klasse_first_five.pdf'
WHERE id = 2;
CREATE TABLE benutzer (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          benutzername VARCHAR(50) NOT NULL,
                          email VARCHAR(100) NOT NULL UNIQUE,
                          passwort_hash VARCHAR(255) NOT NULL,
                          rolle ENUM('schüler','lehrer', 'admin') DEFAULT 'lehrer',
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- pw admin, schueler123, lehrer123
INSERT INTO benutzer (benutzername, email, passwort_hash, rolle) VALUES
    ('Herr Admin', 'admin@schule.de', '$2y$12$pdScC8wBeJrAtqYZoHVQ5u7JaIofX7mLs6YfylL.NXO7eFU5jgLOa', 'admin'),
    ('Max Schüler', 'schueler@schule.de', '$2y$12$RHpmy9hwGAowFZM.vc4lcudWJNnqiTyv0qNJhvEOxMzdFbn6ivlEi', 'schüler'),
    ('Frau Lehrer', 'lehrer@schule.de', '$2y$12$QoFcIEm7FupfA.h28h3qIec4EVVS2wrkph9TwaQ/OWYvLgbU4V5Gy', 'lehrer');


use lernwebseite;

ALTER TABLE lerninhalte ADD COLUMN exercise_style VARCHAR(20);



-- Tabelle für Favoriten
CREATE TABLE favoriten (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           benutzer_id INT NOT NULL,
                           hauptfach_id INT NOT NULL,
                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                           UNIQUE (benutzer_id, hauptfach_id)
);

CREATE TABLE favoriten_unterthemen (
                                       id INT AUTO_INCREMENT PRIMARY KEY,
                                       benutzer_id INT NOT NULL,
                                       unterthema_id INT NOT NULL,
                                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                       UNIQUE (benutzer_id, unterthema_id)
);

-- Tabelle für Verknüpfungen zwischen Lerninhalten (welche Übung/Video gehört zu welcher Erklärung)
CREATE TABLE lerninhalt_verknuepfungen (
                                           id INT AUTO_INCREMENT PRIMARY KEY,
                                           lerninhalt_id INT NOT NULL,
                                           verknuepfter_lerninhalt_id INT NOT NULL,
                                           FOREIGN KEY (lerninhalt_id) REFERENCES lerninhalte(id) ON DELETE CASCADE,
                                           FOREIGN KEY (verknuepfter_lerninhalt_id) REFERENCES lerninhalte(id) ON DELETE CASCADE,
                                           UNIQUE (lerninhalt_id, verknuepfter_lerninhalt_id)
);


