-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mer. 13 nov. 2019 à 21:51
-- Version du serveur :  5.7.27
-- Version de PHP :  7.1.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
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
-- Structure de la table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` tinyint(1) DEFAULT NULL COMMENT 'Activé/Désactivé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `config`
--

INSERT INTO `config` (`id`, `name`, `value`) VALUES
(1, 'controle_osmolateur', 1),
(2, 'controle_ecumeur', 1),
(3, 'controle_bailling', 1),
(4, 'controle_reacteur', 1),
(5, 'controle_temperature', 1),
(6, 'ventilateur_reacteur', 1),
(7, 'cron', 1),
(8, 'cron_temperature', 1),
(9, 'cron_rappel', 1),
(10, 'cron_mail', 1);

-- --------------------------------------------------------

--
-- Structure de la table `controle`
--

CREATE TABLE `controle` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `controle`
--

INSERT INTO `controle` (`id`, `created_at`, `value`) VALUES
(139782, '2019-11-13 22:04:59', 'controle_ecumeur'),
(139783, '2019-09-17 17:25:00', 'controle_osmolateur'),
(139784, '2019-09-17 17:25:00', 'controle_bailling'),
(139786, '2019-11-13 22:03:59', 'controle_reacteur'),
(139823, '2019-11-13 22:45:39', 'controle_temperature');

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
-- Déchargement des données de la table `log`
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
(15, '2019-11-13 22:45:39', 'Temperature - ERREUR - Trop chaud 28.456°C');

-- --------------------------------------------------------

--
-- Structure de la table `osmolateur`
--

CREATE TABLE `osmolateur` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `state` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Déchargement des données de la table `reacteur`
--

INSERT INTO `reacteur` (`id`, `created_at`, `value`) VALUES
(1, '2019-11-13 22:16:31', 1345);

-- --------------------------------------------------------

--
-- Structure de la table `state`
--

CREATE TABLE `state` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `path` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `message` text,
  `mail_send` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `state`
--

INSERT INTO `state` (`id`, `created_at`, `path`, `value`, `error`, `message`, `mail_send`) VALUES
(881, '2019-09-15 21:02:21', 'ecumeur', '1', 0, '', 0),
(883, '2019-09-17 16:56:33', 'bailling', '111', 0, '', 0),
(916, '2019-11-13 21:41:06', 'controle_bailling', 'state_2', 1, 'Cron - Erreur script bailling', 0),
(918, '2019-11-13 21:41:26', 'controle_osmolateur', 'state_2', 1, 'Cron - Erreur script osmolateur', 0),
(921, '2019-11-13 21:41:46', 'controle', 'state_1', 0, 'Cron controle - OK', 0),
(922, '2019-11-13 21:50:21', 'controle_temperature', 'state_2', 1, 'Cron - Erreur script température', 0),
(925, '2019-11-13 22:06:00', 'controle_reacteur', 'state_2', 1, 'Cron - Erreur script réacteur', 0),
(926, '2019-11-13 22:08:25', 'controle_ecumeur', 'state_2', 1, 'Cron - Erreur script écumeur', 0),
(927, '2019-11-13 22:45:39', 'temperature', 'state_6', 1, 'Temperature - ERREUR - Trop chaud 28.456°C', 0);

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
-- Déchargement des données de la table `temperature`
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
(30, '2019-11-13 22:45:39', 28.456);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `config`
--
ALTER TABLE `config`
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
-- Index pour la table `temperature`
--
ALTER TABLE `temperature`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `controle`
--
ALTER TABLE `controle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139824;

--
-- AUTO_INCREMENT pour la table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `osmolateur`
--
ALTER TABLE `osmolateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reacteur`
--
ALTER TABLE `reacteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `state`
--
ALTER TABLE `state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=928;

--
-- AUTO_INCREMENT pour la table `temperature`
--
ALTER TABLE `temperature`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
