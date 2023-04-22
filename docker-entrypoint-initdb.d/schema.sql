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

CREATE TABLE `riddles`
(
    `riddle_id`   INT          NOT NULL AUTO_INCREMENT,
    `user_id`    INT          NOT NULL,
    `riddle`      VARCHAR(255) NOT NULL,
    `answer`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`riddle_id`),
    FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams`
(
    `team_id`   INT NOT NULL,
    `user_id_1`    INT,
    `user_id_2`    INT,
    `score`      INT NOT NULL,
    FOREIGN KEY (user_id_1) REFERENCES users (id),
    FOREIGN KEY (user_id_2) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;