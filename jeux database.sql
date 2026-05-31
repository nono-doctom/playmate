CREATE TABLE jeu(
   id_jeu INT AUTO_INCREMENT,
   nom VARCHAR(50),
   description VARCHAR(50),
   date_sortie_jeu DATE,
   PRIMARY KEY(id_jeu)
);



CREATE TABLE plateforme(
   id_plateforme INT AUTO_INCREMENT,
   libelle VARCHAR(50),
   description VARCHAR(255),
   PRIMARY KEY(id_plateforme)
);

CREATE TABLE genre_jeu(
   id_genre INT AUTO_INCREMENT,
   nom VARCHAR(50),
   description VARCHAR(255),
   PRIMARY KEY(id_genre),
   UNIQUE(nom)
);

CREATE TABLE personnage(
   nom_personnage VARCHAR(50),
   description VARCHAR(50),
   PRIMARY KEY(nom_personnage)
);

CREATE TABLE Utilisateur(
   id_user INT AUTO_INCREMENT,
   pseudo VARCHAR(50) NOT NULL,
   email VARCHAR(100) NOT NULL,
   mot_de_passe VARCHAR(255) NOT NULL,
   date_naissance DATE,
   bio VARCHAR(200),
   date_inscription DATE,
   humeur VARCHAR(50),
   nom_personnage VARCHAR(50) NOT NULL,
   PRIMARY KEY(id_user),
   FOREIGN KEY(nom_personnage) REFERENCES personnage(nom_personnage)
);

CREATE TABLE jouer(
   id_jeu INT,
   id_user INT,
   id_plateforme INT,
   nb_heure_joue INT,  -- corrigé de DATE à INT
   PRIMARY KEY(id_jeu, id_user, id_plateforme),
   FOREIGN KEY(id_jeu) REFERENCES jeu(id_jeu),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user),
   FOREIGN KEY(id_plateforme) REFERENCES plateforme(id_plateforme)
);
CREATE TABLE Message(
   id_message INT AUTO_INCREMENT,
   contenu VARCHAR(250),
   date_mess DATETIME DEFAULT CURRENT_TIMESTAMP,
   id_user INT NOT NULL,
   id_user_1 INT NOT NULL,
   PRIMARY KEY(id_message),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user),
   FOREIGN KEY(id_user_1) REFERENCES Utilisateur(id_user)
);


CREATE TABLE Matcher(
   id_user INT,
   id_user_1 INT,
   avis VARCHAR(50),
   PRIMARY KEY(id_user, id_user_1),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user),
   FOREIGN KEY(id_user_1) REFERENCES Utilisateur(id_user)
);

CREATE TABLE utiliser(
   id_user INT,
   id_plateforme INT,
   PRIMARY KEY(id_user, id_plateforme),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user),
   FOREIGN KEY(id_plateforme) REFERENCES plateforme(id_plateforme)
);

CREATE TABLE avoir(
   id_jeu INT,
   nom VARCHAR(50),
   PRIMARY KEY(id_jeu, nom),
   FOREIGN KEY(id_jeu) REFERENCES jeu(id_jeu),
   FOREIGN KEY(nom) REFERENCES genre_jeu(nom)
);

CREATE TABLE sortir(
   id_jeu INT,
   id_plateforme INT,
   PRIMARY KEY(id_jeu, id_plateforme),
   FOREIGN KEY(id_jeu) REFERENCES jeu(id_jeu),
   FOREIGN KEY(id_plateforme) REFERENCES plateforme(id_plateforme)
);

CREATE TABLE Recevoir(
   id_user INT,
   id INT,
   PRIMARY KEY(id_user, id),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user),
   FOREIGN KEY(id) REFERENCES Message(id_message)  -- corrigé
);

-- Insertion genres
INSERT INTO genre_jeu (nom) VALUES
('FPS'),
('MOBA'),
('RPG'),
('Simulation'),
('Aventure'),
('Chill');

-- Insertion jeux
INSERT INTO jeu (nom) VALUES
('Fortnite'),
('League of Legends'),
('Valorant'),
('Minecraft'),
('Apex Legends'),
('FIFA'),
('Call of Duty'),
('Overwatch'),
('Rocket League'),
('PUBG'),
('Counter-Strike 2'),
('Dota 2'),
('Among Us'),
('The Legend of Zelda: Tears of the Kingdom'),
('Elden Ring'),
('Grand Theft Auto V'),
('Red Dead Redemption 2'),
('Cyberpunk 2077'),
('The Witcher 3: Wild Hunt'),
('Rainbow Six Siege'),
('Genshin Impact'),
('Honkai: Star Rail'),
('Starfield'),
('Baldur''s Gate 3'),
('The Sims 4'),
('Animal Crossing: New Horizons'),
('Super Smash Bros. Ultimate'),
('Splatoon 3'),
('Persona 5 Royal'),
('Assassin''s Creed Mirage'),
('Destiny 2'),
('Fall Guys'),
('Terraria'),
('Rust'),
('ARK: Survival Evolved'),
('Palworld'),
('Helldivers 2'),
('No Man''s Sky'),
('Diablo IV'),
('Skyrim'),
('Hogwarts Legacy');
INSERT INTO `personnage` (`nom_personnage`, `description`) VALUES
('Une Lionnette', "Je tryhard ici c'est la victoire"),
('Un Pinguin', "J'aime glisser et m'amuser sur les jeux"),
('Un Pijaune', "Je dépense beaucoup dans les jeux"),
('Un Chatou', "Je cherche l’amour dans les jeux")
ON DUPLICATE KEY UPDATE description=VALUES(description);
INSERT INTO plateforme (libelle, description) VALUES
('PC', 'Ordinateur personnel'),
('PlayStation', 'PlayStation'),
('Xbox', 'Console Xbox'),
('Switch', 'Nintendo Switch'),
('Mobile','Mobile');
UPDATE jeu SET date_sortie_jeu = '2017-07-25' WHERE nom = 'Fortnite';
UPDATE jeu SET date_sortie_jeu = '2009-10-27' WHERE nom = 'League of Legends';
UPDATE jeu SET date_sortie_jeu = '2020-06-02' WHERE nom = 'Valorant';
UPDATE jeu SET date_sortie_jeu = '2011-11-18' WHERE nom = 'Minecraft';
UPDATE jeu SET date_sortie_jeu = '2019-02-04' WHERE nom = 'Apex Legends';
UPDATE jeu SET date_sortie_jeu = '2023-09-29' WHERE nom = 'FIFA';
UPDATE jeu SET date_sortie_jeu = '2003-10-29' WHERE nom = 'Call of Duty';
UPDATE jeu SET date_sortie_jeu = '2016-05-24' WHERE nom = 'Overwatch';
UPDATE jeu SET date_sortie_jeu = '2015-07-07' WHERE nom = 'Rocket League';
UPDATE jeu SET date_sortie_jeu = '2017-12-20' WHERE nom = 'PUBG';
UPDATE jeu SET date_sortie_jeu = '2023-09-27' WHERE nom = 'Counter-Strike 2';
UPDATE jeu SET date_sortie_jeu = '2013-07-09' WHERE nom = 'Dota 2';
UPDATE jeu SET date_sortie_jeu = '2018-06-15' WHERE nom = 'Among Us';
UPDATE jeu SET date_sortie_jeu = '2023-05-12' WHERE nom = 'The Legend of Zelda: Tears of the Kingdom';
UPDATE jeu SET date_sortie_jeu = '2022-02-25' WHERE nom = 'Elden Ring';
UPDATE jeu SET date_sortie_jeu = '2013-09-17' WHERE nom = 'Grand Theft Auto V';
UPDATE jeu SET date_sortie_jeu = '2018-10-26' WHERE nom = 'Red Dead Redemption 2';
UPDATE jeu SET date_sortie_jeu = '2020-12-10' WHERE nom = 'Cyberpunk 2077';
UPDATE jeu SET date_sortie_jeu = '2015-05-19' WHERE nom = 'The Witcher 3: Wild Hunt';
UPDATE jeu SET date_sortie_jeu = '2015-12-01' WHERE nom = 'Rainbow Six Siege';
UPDATE jeu SET date_sortie_jeu = '2020-09-28' WHERE nom = 'Genshin Impact';
UPDATE jeu SET date_sortie_jeu = '2023-04-26' WHERE nom = 'Honkai: Star Rail';
UPDATE jeu SET date_sortie_jeu = '2023-09-06' WHERE nom = 'Starfield';
UPDATE jeu SET date_sortie_jeu = '2023-08-03' WHERE nom = 'Baldur''s Gate 3';
UPDATE jeu SET date_sortie_jeu = '2014-09-02' WHERE nom = 'The Sims 4';
UPDATE jeu SET date_sortie_jeu = '2020-03-20' WHERE nom = 'Animal Crossing: New Horizons';
UPDATE jeu SET date_sortie_jeu = '2018-12-07' WHERE nom = 'Super Smash Bros. Ultimate';
UPDATE jeu SET date_sortie_jeu = '2022-09-09' WHERE nom = 'Splatoon 3';
UPDATE jeu SET date_sortie_jeu = '2019-10-31' WHERE nom = 'Persona 5 Royal';
UPDATE jeu SET date_sortie_jeu = '2023-10-05' WHERE nom = 'Assassin''s Creed Mirage';
UPDATE jeu SET date_sortie_jeu = '2017-09-06' WHERE nom = 'Destiny 2';
UPDATE jeu SET date_sortie_jeu = '2020-08-04' WHERE nom = 'Fall Guys';
UPDATE jeu SET date_sortie_jeu = '2011-05-16' WHERE nom = 'Terraria';
UPDATE jeu SET date_sortie_jeu = '2018-02-08' WHERE nom = 'Rust';
UPDATE jeu SET date_sortie_jeu = '2017-08-29' WHERE nom = 'ARK: Survival Evolved';
UPDATE jeu SET date_sortie_jeu = '2024-01-19' WHERE nom = 'Palworld';
UPDATE jeu SET date_sortie_jeu = '2024-02-08' WHERE nom = 'Helldivers 2';
UPDATE jeu SET date_sortie_jeu = '2016-08-09' WHERE nom = 'No Man''s Sky';
UPDATE jeu SET date_sortie_jeu = '2023-06-06' WHERE nom = 'Diablo IV';
UPDATE jeu SET date_sortie_jeu = '2011-11-11' WHERE nom = 'Skyrim';
UPDATE jeu SET date_sortie_jeu = '2023-02-10' WHERE nom = 'Hogwarts Legacy';
DELIMITER //

CREATE TRIGGER verifier_sortie_jeu
BEFORE INSERT ON sortir
FOR EACH ROW
BEGIN
    DECLARE dateSortie DATE;

    -- on récupère la date de sortie du jeu
    SELECT date_sortie_jeu 
    INTO dateSortie
    FROM jeu
    WHERE id_jeu = NEW.id_jeu;

    -- si le jeu n’est pas encore sorti → erreur
    IF dateSortie IS NULL OR dateSortie > CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ce jeu n’est pas encore sorti sur cette plateforme';
    END IF;

END//

DELIMITER ;
INSERT INTO sortir (id_jeu, id_plateforme) VALUES

-- Fortnite
(1,1),(1,2),(1,3),(1,4),(1,5),

-- League of Legends
(2,1),

-- Valorant
(3,1),

-- Minecraft
(4,1),(4,2),(4,3),(4,4),(4,5),

-- Apex Legends
(5,1),(5,2),(5,3),

-- FIFA
(6,2),(6,3),

-- Call of Duty
(7,1),(7,2),(7,3),

-- Overwatch
(8,1),(8,2),(8,3),

-- Rocket League
(9,1),(9,2),(9,3),(9,4),(9,5),

-- PUBG
(10,1),(10,2),(10,3),(10,5),

-- Counter-Strike 2
(11,1),

-- Dota 2
(12,1),

-- Among Us
(13,1),(13,2),(13,3),(13,4),(13,5),

-- Zelda Tears of the Kingdom
(14,4),

-- Elden Ring
(15,1),(15,2),(15,3),

-- GTA V
(16,1),(16,2),(16,3),

-- Red Dead Redemption 2
(17,1),(17,2),(17,3),

-- Cyberpunk 2077
(18,1),(18,2),(18,3),

-- Witcher 3
(19,1),(19,2),(19,3),(19,4),(19,5),

-- Rainbow Six Siege
(20,1),(20,2),(20,3),

-- Genshin Impact
(21,1),(21,2),(21,3),(21,4),(21,5),

-- Honkai Star Rail
(22,1),(22,2),(22,3),(22,5),

-- Starfield
(23,1),(23,3),

-- Baldur's Gate 3
(24,1),(24,2),(24,3),

-- Sims 4
(25,1),

-- Animal Crossing
(26,4),

-- Smash Bros
(27,4),

-- Splatoon 3
(28,4),

-- Persona 5 Royal
(29,2),(29,4),

-- Assassin's Creed Mirage
(30,1),(30,2),(30,3),

-- Destiny 2
(31,1),(31,2),(31,3),

-- Fall Guys
(32,1),(32,2),(32,3),(32,4),(32,5),

-- Terraria
(33,1),(33,2),(33,3),(33,4),(33,5),

-- Rust
(34,1),

-- ARK
(35,1),(35,2),(35,3),

-- Palworld
(36,1),

-- Helldivers 2
(37,1),(37,2),(37,3),

-- No Man's Sky
(38,1),(38,2),(38,3),

-- Diablo IV
(39,1),(39,2),(39,3),

-- Skyrim
(40,1),(40,2),(40,3),

-- Hogwarts Legacy
(41,1),(41,2),(41,3);
ALTER TABLE Utilisateur ADD photo VARCHAR(255) DEFAULT NULL;