-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Lun 31 Août 2020 à 13:06
-- Version du serveur :  5.7.31-0ubuntu0.18.04.1
-- Version de PHP :  7.2.24-0ubuntu0.18.04.6

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données :  `aqua-web`
--

--
-- Contenu de la table `data_depense`
--

INSERT INTO `data_depense` (`id`, `created_at`, `comment`, `value`) VALUES
(3, '2019-08-02 00:00:00', 'EUROPRIX', 7),
(4, '2019-08-03 00:00:00', 'Commande ZOANTHUS', 92.15),
(5, '2019-08-05 00:00:00', 'EUROPRIX', 2),
(6, '2019-09-14 00:00:00', 'POISSON D OR', 19.61),
(7, '2020-02-03 00:00:00', 'Commande ZOANTHUS', 51.31),
(8, '2020-03-17 00:00:00', 'EUROPRIX', 6),
(9, '2020-04-17 00:00:00', 'EUROPRIX', 20.25),
(10, '2020-05-07 00:00:00', 'EUROPRIX', 3.75),
(11, '2020-05-23 00:00:00', 'EUROPRIX', 0.59),
(12, '2020-05-29 00:00:00', 'EUROPRIX', 141.1),
(13, '2020-06-08 00:00:00', 'EUROPRIX', 1.18),
(14, '2020-06-15 00:00:00', 'EUROPRIX', 13.75),
(15, '2020-06-27 00:00:00', 'EUROPRIX', 1.72),
(16, '2020-07-04 00:00:00', 'EUROPRIX', 10.5),
(17, '2020-07-17 00:00:00', 'EUROPRIX', 4),
(18, '2020-07-25 00:00:00', 'EUROPRIX', 3.79),
(19, '2020-08-01 00:00:00', 'EUROPRIX', 2),
(20, '2020-08-14 00:00:00', 'EUROPRIX', 2),
(21, '2020-08-24 00:00:00', 'EUROPRIX', 6.5),
(22, '2020-08-22 00:00:00', 'Commande ZOANTHUS', 82.82),
(23, '2019-12-26 00:00:00', 'EUROPRIX', 3),
(24, '2019-12-21 00:00:00', 'EUROPRIX', 36.25),
(25, '2019-12-17 00:00:00', 'EUROPRIX', 27.8),
(26, '2020-01-20 00:00:00', 'RECIFALNEWS', 35.46),
(27, '2020-01-06 00:00:00', 'EUROPRIX', 0.71),
(28, '2019-12-16 00:00:00', 'EUROPRIX', 2),
(29, '2019-12-02 00:00:00', 'EUROPRIX', 3),
(30, '2019-12-01 00:00:00', 'Commande ZOANTHUS', 72.5),
(31, '2019-11-08 00:00:00', 'EUROPRIX', 0.29),
(32, '2019-10-19 00:00:00', 'EUROPRIX', 2),
(33, '2019-09-27 00:00:00', 'EUROPRIX', 35.25),
(34, '2019-09-21 00:00:00', 'EUROPRIX', 2),
(35, '2019-01-11 00:00:00', 'EUROPRIX', 14.3),
(36, '2019-01-05 00:00:00', 'EUROPRIX', 19.85);
SET FOREIGN_KEY_CHECKS=1;

ALTER TABLE `core_config` ADD `show_time_left` BOOLEAN NOT NULL AFTER `label`;

UPDATE `core_config` SET `show_time_left` = '1' WHERE `core_config`.`id` = 5;
UPDATE `core_config` SET `show_time_left` = '1' WHERE `core_config`.`id` = 6;
UPDATE `core_config` SET `show_time_left` = '1' WHERE `core_config`.`id` = 7;
UPDATE `core_config` SET `show_time_left` = '1' WHERE `core_config`.`id` = 8;
UPDATE `core_config` SET `show_time_left` = '1' WHERE `core_config`.`id` = 9;