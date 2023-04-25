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
    `id`        INT                                                     NOT NULL AUTO_INCREMENT,
    `email`     VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `password`  VARCHAR(255)                                            NOT NULL,
    `createdAt` DATETIME                                                NOT NULL,
    `updatedAt` DATETIME                                                NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `riddles`;
CREATE TABLE `riddles`
(
    `riddle_id`   INT          NOT NULL AUTO_INCREMENT,
    `user_id`    INT,
    `riddle`      VARCHAR(255) NOT NULL,
    `answer`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`riddle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams`
(
    `team_id`   INT NOT NULL AUTO_INCREMENT,
    `user_id_1`    INT,
    `user_id_2`    INT,
    `score`      INT NOT NULL,
    PRIMARY KEY (`team_id`),
    FOREIGN KEY (user_id_1) REFERENCES users (id),
    FOREIGN KEY (user_id_2) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
DROP TABLE IF EXISTS `riddles`;
CREATE TABLE `riddles`
(
    `riddle_id`   INT          NOT NULL AUTO_INCREMENT,
    `user_id`    INT,
    `riddle`      VARCHAR(255) NOT NULL,
    `answer`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`riddle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `teams`;

DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`
(
    `id`        INT                                                     NOT NULL AUTO_INCREMENT,
    `email`     VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `password`  VARCHAR(255)                                            NOT NULL,
    `createdAt` DATETIME                                                NOT NULL,
    `updatedAt` DATETIME                                                NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members`
(
    `members_id`   INT NOT NULL AUTO_INCREMENT,
    `user_id_1`    INT,
    `user_id_2`    INT,
    PRIMARY KEY (`members_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `teamstemp`;
CREATE TABLE `teamstemp`
(
    `team_id`   INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255),
    `players_count` INT NOT NULL,
    `members_id` INT NOT NULL,
    `score`      INT NOT NULL,
    PRIMARY KEY (`team_id`),
    FOREIGN KEY (members_id) REFERENCES members (members_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/