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