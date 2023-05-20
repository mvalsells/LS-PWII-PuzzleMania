SET NAMES utf8;
SET
time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS `puzzlemania`;
USE `puzzlemania`;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`
(
    `id`                 INT                                                     NOT NULL AUTO_INCREMENT,
    `email`              VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `password`           VARCHAR(255)                                            NOT NULL,
    `profilePicturePath` VARCHAR(255),
    `createdAt`          DATETIME                                                NOT NULL,
    `updatedAt`          DATETIME                                                NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `riddles`;
CREATE TABLE `riddles`
(
    `riddle_id`   INT NOT NULL AUTO_INCREMENT,
    `user_id`     INT,
    `riddle`      VARCHAR(255) NOT NULL,
    `answer`      VARCHAR(255) NOT NULL,
    PRIMARY KEY (`riddle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams`
(
    `team_id`         INT NOT NULL AUTO_INCREMENT,
    `team_name`       VARCHAR(255) NOT NULL,
    `num_members`     INT NOT NULL,
    `user_id_1`       INT NOT NULL,
    `user_id_2`       INT,
    `total_score`     INT NOT NULL,
    `last_score`      INT,
    `QR_generated`    INT NOT NULL,
    PRIMARY KEY (`team_id`),
    FOREIGN KEY (user_id_1) REFERENCES users (id),
    FOREIGN KEY (user_id_2) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games`
(
    `game_id`       INT NOT NULL AUTO_INCREMENT,
    `user_id`       INT NOT NULL,
    `riddle_1`      INT NOT NULL,
    `riddle_2`      INT NOT NULL,
    `riddle_3`      INT NOT NULL,
    PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- INSERT DEMO/TEST DATA TO USERS TABLE
INSERT INTO users (email, password, createdAt, updatedAt)
VALUES
    ('david@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('marc@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('irene@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('nerea@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('oscar@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('hector@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('guillem@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('albert@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('gerard@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('marta@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('claudia@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('carla@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('julia@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW()),
    ('roger@salle.url.edu', '392a15f4b7655145158b25b34e56bcd3', NOW(), NOW());

-- INSERT DEMO/TEST DATA TO USERS TABLE
INSERT INTO teams (team_name, num_members, user_id_1, user_id_2, total_score, last_score, QR_generated)
VALUES
    ('Bests', 1, 1, NULL, 0, NULL, 0),
    ('Messi', 2, 2, 3, 20, 10, 0),
    ('TeamStar', 1, 4, NULL, 10, 10, 0),
    ('VivaPHP', 1, 5, NULL, 10, 0, 0),
    ('Los Grandes', 1, 6, NULL, 0, NULL, 0);


-- INSERT DEMO/TEST DATA TO RIDDLES TABLE
INSERT INTO riddles (user_id, riddle, answer)
VALUES
    (1, 'It brings back the lost as though never gone, shines laughter and tears with light long since shone; a moment to make, a lifetime to shed; valued then but lost when your dead. What Is It?', 'Memory'),
    (NULL, 'What do you get when you cross a fish with an elephant?', 'Swimming trunks'),
    (2, 'I can be long, or I can be short.\nI can be grown, and I can be bought.\nI can be painted, or left bare.\nI can be round, or I can be square.\nWhat am I?', 'Fingernails'),
    (NULL, 'I am lighter than a feather yet no man can hold me for long.', 'Breath'),
    (NULL, 'What occurs once in every minute, twice in every moment and yet never in a thousand years?', 'The letter M'),
    (NULL, 'What nationality is Santa Claus?', 'North Polish'),
    (NULL, 'What animal is best at hitting a baseball?', 'A bat'),
    (NULL, 'What do you call a cow that twitches?', 'Beef jerky');



