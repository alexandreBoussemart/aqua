-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Mer 20 Novembre 2019 à 16:31
-- Version du serveur :  5.7.28-0ubuntu0.18.04.4
-- Version de PHP :  7.2.24-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `aqua-web`
--

-- --------------------------------------------------------

--
-- Structure de la table `changement_eau`
--

CREATE TABLE `changement_eau` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `changement_eau`
--

INSERT INTO `changement_eau` (`id`, `created_at`, `value`) VALUES
(2, '2019-11-20 13:51:06', 20),
(3, '2019-11-20 14:02:37', 34),
(4, '2019-11-20 14:07:55', 31);

-- --------------------------------------------------------

--
-- Structure de la table `controle`
--

CREATE TABLE `controle` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `value` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `controle`
--

INSERT INTO `controle` (`id`, `created_at`, `value`, `label`) VALUES
(139782, '2019-11-13 22:04:59', 'controle_ecumeur', 'Écumeur'),
(139783, '2019-09-17 17:25:00', 'controle_osmolateur', 'Osmolateur'),
(139784, '2019-09-17 17:25:00', 'controle_bailling', 'Bailling'),
(139786, '2019-11-14 22:30:59', 'controle_reacteur', 'Réacteur'),
(139824, '2019-11-20 09:57:13', 'controle_temperature', 'Température');

-- --------------------------------------------------------

--
-- Structure de la table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `log`
--

INSERT INTO `log` (`id`, `created_at`, `message`) VALUES
(1, '2019-11-13 21:13:36', 'Cron temperature - ERREUR - Le fichier : /sys/bus/w1/devices/28-0213191aabaa/w1_slave n\'existe pas.'),
(2, '2019-11-13 21:14:52', 'Temperature - ERREUR - Trop chaud 28.456°C'),
(3, '2019-11-13 21:16:17', 'Temperature - ERREUR - Trop froid 22.456°C'),
(4, '2019-11-13 21:41:06', 'Cron - Erreur script bailling'),
(5, '2019-11-13 21:41:16', 'Cron - Erreur script écumeur'),
(6, '2019-11-13 21:41:26', 'Cron - Erreur script osmolateur'),
(7, '2019-11-13 21:41:36', 'Cron - Erreur script réacteur'),
(8, '2019-11-13 21:41:46', 'Cron - Erreur script température'),
(9, '2019-11-13 21:41:46', 'Cron controle - OK'),
(10, '2019-11-13 21:50:21', 'Cron - Erreur script température'),
(11, '2019-11-13 22:04:35', 'Cron controle controle_reacteur - OK'),
(12, '2019-11-13 22:05:40', 'Cron controle controle_ecumeur - OK'),
(13, '2019-11-13 22:06:00', 'Cron - Erreur script réacteur'),
(14, '2019-11-13 22:08:25', 'Cron - Erreur script écumeur'),
(15, '2019-11-13 22:45:39', 'Temperature - ERREUR - Trop chaud 28.456°C'),
(16, '2019-11-14 22:26:59', 'Temperature - ERREUR - Trop froid 22.456°C'),
(17, '2019-11-14 22:29:06', 'Temperature - ERREUR - Trop froid 22.456°C'),
(18, '2019-11-14 22:30:31', 'Cron controle controle_reacteur - OK'),
(19, '2019-11-14 22:32:41', 'Cron - Erreur script réacteur'),
(20, '2019-11-14 22:39:17', 'Temperature - ERREUR - Trop chaud 28.456°C'),
(21, '2019-11-14 22:41:53', 'Temperature - ERREUR - Trop chaud 28.456°C'),
(22, '2019-11-14 22:42:24', 'Temperature - OK -  25.456°C'),
(23, '2019-11-14 22:50:56', 'Cron controle - OK'),
(24, '2019-11-15 12:20:22', 'Temperature - ERREUR - Trop froid 22.456Â°C'),
(25, '2019-11-15 12:25:47', 'Temperature - ERREUR - Trop chaud 28.456Â°C'),
(26, '2019-11-15 12:27:12', 'Temperature - ERREUR - Trop froid 22.456Ã‚Â°C'),
(27, '2019-11-15 13:27:39', 'Temperature - RAPPEL ERREUR - Trop froid 22.456Ã‚Â°C'),
(28, '2019-11-15 13:28:07', 'Temperature - RAPPEL ERREUR - Trop froid 22.456Ã‚Â°C'),
(29, '2019-11-15 13:31:05', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(30, '2019-11-15 13:32:17', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(31, '2019-11-15 13:36:17', 'Cron - Erreur script Ã©cumeur'),
(32, '2019-11-15 13:36:27', 'Cron - Erreur script osmolateur'),
(33, '2019-11-15 13:36:37', 'Cron - Erreur script rÃ©acteur'),
(34, '2019-11-15 13:36:47', 'Cron - Erreur script tempÃ©rature'),
(35, '2019-11-15 13:36:47', 'Cron controle - OK'),
(36, '2019-11-15 13:38:19', 'Temperature - ERREUR - Trop froid 22.456Â°C'),
(37, '2019-11-15 13:38:56', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(38, '2019-11-15 13:38:56', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(39, '2019-11-15 13:38:56', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(40, '2019-11-15 13:38:56', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(41, '2019-11-15 13:38:56', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(42, '2019-11-15 13:38:56', 'Address in mailbox given [] does not comply with RFC 2822, 3.6.2.'),
(43, '2019-11-15 13:40:08', 'Cron controle controle_temperature - OK'),
(44, '2019-11-15 13:40:08', 'Cron controle - OK'),
(45, '2019-11-15 13:41:50', 'Cron - Erreur script tempÃ©rature'),
(46, '2019-11-15 13:41:50', 'Cron controle - OK'),
(47, '2019-11-15 13:41:50', 'Cron controle - OK'),
(48, '2019-11-15 13:43:26', 'Cron controle - ERREUR - '),
(49, '2019-11-15 13:44:36', 'Cron controle - OK'),
(50, '2019-11-15 13:44:36', 'Cron controle - OK'),
(51, '2019-11-20 09:54:33', 'Cron controle controle_temperature - OK'),
(52, '2019-11-20 09:54:33', 'Cron controle - OK'),
(53, '2019-11-20 09:57:13', 'Temperature - OK -  25.456Â°C'),
(54, '2019-11-20 09:59:53', 'Cron - Erreur script tempÃ©rature'),
(55, '2019-11-20 09:59:53', 'Cron controle - OK');

-- --------------------------------------------------------

--
-- Structure de la table `osmolateur`
--

CREATE TABLE `osmolateur` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `state` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `osmolateur`
--

INSERT INTO `osmolateur` (`id`, `created_at`, `state`) VALUES
(1, '2019-11-20 09:48:47', 'ok');

-- --------------------------------------------------------

--
-- Structure de la table `reacteur`
--

CREATE TABLE `reacteur` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `reacteur`
--

INSERT INTO `reacteur` (`id`, `created_at`, `value`) VALUES
(1, '2019-11-13 22:16:31', 1345);

-- --------------------------------------------------------

--
-- Structure de la table `state`
--

CREATE TABLE `state` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `path` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `message` text,
  `mail_send` tinyint(1) NOT NULL DEFAULT '0',
  `exclude_check` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `state`
--

INSERT INTO `state` (`id`, `created_at`, `path`, `value`, `error`, `message`, `mail_send`, `exclude_check`) VALUES
(881, '2019-09-15 21:02:21', 'ecumeur', '1', 0, '', 0, 1),
(883, '2019-09-17 16:56:33', 'bailling', '111', 0, '', 0, 1),
(916, '2019-11-15 13:38:56', 'controle_bailling', 'state_2', 1, 'Cron - Erreur script bailling', 1, 0),
(918, '2019-11-15 13:38:56', 'controle_osmolateur', 'state_2', 1, 'Cron - Erreur script osmolateur', 1, 0),
(919, '2019-11-20 09:59:53', 'controle_temperature', 'state_2', 1, 'Cron - Erreur script tempÃ©rature', 0, 0),
(925, '2019-11-15 13:38:56', 'controle_reacteur', 'state_2', 1, 'Cron - Erreur script rÃ©acteur', 1, 0),
(926, '2019-11-15 13:38:56', 'controle_ecumeur', 'state_2', 1, 'Cron - Erreur script Ã©cumeur', 1, 0),
(927, '2019-11-20 09:57:13', 'temperature', 'state_7', 0, 'Temperature - OK -  25.456Â°C', 0, 0),
(928, '2019-11-15 13:44:36', 'controle', 'state_1', 0, 'Cron controle - OK', 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` tinyint(1) DEFAULT NULL COMMENT 'Activé/Désactivé',
  `label` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `status`
--

INSERT INTO `status` (`id`, `name`, `value`, `label`) VALUES
(1, 'osmolateur', 0, 'Osmolateur'),
(2, 'ecumeur', 1, 'Écumeur'),
(3, 'bailling', 0, 'Bailling'),
(4, 'reacteur', 1, 'Réacteur'),
(5, 'temperature', 1, 'Température'),
(6, 'reacteur_ventilateur', 1, 'Ventilateur réacteur'),
(7, 'reacteur_eclairage', 0, 'Éclairage réacteur'),
(8, 'cron_controle', 1, 'Cron check contrôle'),
(9, 'cron_temperature', 1, 'Cron température'),
(10, 'cron_rappel', 1, 'Cron rappel'),
(11, 'cron_mail', 0, 'Cron email'),
(12, 'refroidissement', 1, 'Refroidissement');

-- --------------------------------------------------------

--
-- Structure de la table `temperature`
--

CREATE TABLE `temperature` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `temperature`
--

INSERT INTO `temperature` (`id`, `created_at`, `value`) VALUES
(1, '2019-11-13 14:01:47', 20.187),
(2, '2019-11-13 14:02:30', 25.187),
(3, '2019-11-13 14:03:07', 25.187),
(4, '2019-11-13 14:03:17', 25.187),
(5, '2019-11-13 14:03:40', 25.187),
(6, '2019-11-13 14:04:04', 25.187),
(7, '2019-11-13 14:04:18', 28.187),
(8, '2019-11-13 14:05:47', 23.456),
(9, '2019-11-13 14:09:14', 23.456),
(10, '2019-11-13 14:09:24', 23.456),
(11, '2019-11-13 14:11:12', 23.456),
(12, '2019-11-13 14:11:25', 23.456),
(13, '2019-11-13 14:12:14', 22.456),
(14, '2019-11-13 14:12:52', 22.456),
(15, '2019-11-13 14:14:00', 22.456),
(16, '2019-11-13 14:14:05', 22.456),
(17, '2019-11-13 14:14:14', 22.456),
(18, '2019-11-13 14:14:36', 28.456),
(19, '2019-11-13 14:15:12', 28.456),
(20, '2019-11-13 21:14:52', 28.456),
(21, '2019-11-13 21:15:02', 28.456),
(22, '2019-11-13 21:15:34', 28.456),
(23, '2019-11-13 21:16:17', 22.456),
(24, '2019-11-13 21:16:35', 22.456),
(25, '2019-11-13 21:16:38', 22.456),
(26, '2019-11-13 21:16:41', 22.456),
(27, '2019-11-13 21:16:43', 22.456),
(28, '2019-11-13 22:41:02', 22.456),
(29, '2019-11-13 22:44:20', 22.456),
(30, '2019-11-13 22:45:39', 28.456),
(31, '2019-11-14 22:09:34', 28.456),
(32, '2019-11-14 22:10:59', 28.456),
(33, '2019-11-14 22:12:50', 28.456),
(34, '2019-11-14 22:18:08', 28.456),
(35, '2019-11-14 22:18:47', 28.456),
(36, '2019-11-14 22:23:12', 28.456),
(37, '2019-11-14 22:26:59', 22.456),
(38, '2019-11-14 22:27:51', 22.456),
(39, '2019-11-14 22:29:06', 22.456),
(40, '2019-11-14 22:35:27', 22.456),
(41, '2019-11-14 22:36:48', 0.028),
(42, '2019-11-14 22:39:17', 28.456),
(43, '2019-11-14 22:40:54', 28.456),
(44, '2019-11-14 22:41:53', 28.456),
(45, '2019-11-14 22:42:24', 25.456),
(46, '2019-11-15 12:20:22', 22.456),
(47, '2019-11-15 12:25:47', 28.456),
(48, '2019-11-15 12:27:12', 22.456),
(49, '2019-11-15 13:38:18', 22.456),
(50, '2019-11-20 09:57:13', 25.456);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `changement_eau`
--
ALTER TABLE `changement_eau`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `controle`
--
ALTER TABLE `controle`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `osmolateur`
--
ALTER TABLE `osmolateur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reacteur`
--
ALTER TABLE `reacteur`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `temperature`
--
ALTER TABLE `temperature`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `changement_eau`
--
ALTER TABLE `changement_eau`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `controle`
--
ALTER TABLE `controle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139825;
--
-- AUTO_INCREMENT pour la table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
--
-- AUTO_INCREMENT pour la table `osmolateur`
--
ALTER TABLE `osmolateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `reacteur`
--
ALTER TABLE `reacteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `state`
--
ALTER TABLE `state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=929;
--
-- AUTO_INCREMENT pour la table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT pour la table `temperature`
--
ALTER TABLE `temperature`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
