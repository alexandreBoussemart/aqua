-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Mer 13 Novembre 2019 à 14:41
-- Version du serveur :  5.7.27-0ubuntu0.18.04.1
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
-- Structure de la table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` tinyint(1) DEFAULT NULL COMMENT 'Activé/Désactivé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `config`
--

INSERT INTO `config` (`id`, `name`, `value`) VALUES
(1, 'controle_osmolateur', 1),
(2, 'controle_ecumeur', 1),
(3, 'controle_bailling', 1),
(4, 'controle_reacteur', 1),
(5, 'controle_temperature', 1),
(6, 'ventilateur_reacteur', 1),
(7, 'cron', 1),
(8, 'cron_temperature', 1);

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
-- Contenu de la table `controle`
--

INSERT INTO `controle` (`id`, `created_at`, `value`) VALUES
(139782, '2019-09-17 17:25:00', 'controle_ecumeur'),
(139783, '2019-09-17 17:25:00', 'controle_osmolateur'),
(139784, '2019-09-17 17:25:00', 'controle_bailling'),
(139786, '2019-09-17 17:25:04', 'controle_reacteur'),
(139812, '2019-11-13 14:15:12', 'controle_temperature');

-- --------------------------------------------------------

--
-- Structure de la table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
-- Contenu de la table `state`
--

INSERT INTO `state` (`id`, `created_at`, `path`, `value`, `error`, `message`, `mail_send`) VALUES
(881, '2019-09-15 21:02:21', 'ecumeur', '1', 0, '', 0),
(883, '2019-09-17 16:56:33', 'bailling', '111', 0, '', 0),
(912, '2019-11-13 14:14:36', 'temperature', 'state_6', 1, 'Temperature - ERREUR - Trop chaud 28.456Â°C', 0);

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
(19, '2019-11-13 14:15:12', 28.456);

--
-- Index pour les tables exportées
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
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT pour la table `controle`
--
ALTER TABLE `controle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139813;
--
-- AUTO_INCREMENT pour la table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `osmolateur`
--
ALTER TABLE `osmolateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `reacteur`
--
ALTER TABLE `reacteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `state`
--
ALTER TABLE `state`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=913;
--
-- AUTO_INCREMENT pour la table `temperature`
--
ALTER TABLE `temperature`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
