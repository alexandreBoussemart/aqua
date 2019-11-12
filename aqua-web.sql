-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mar. 12 nov. 2019 à 22:30
-- Version du serveur :  5.7.27
-- Version de PHP :  7.1.26

SET FOREIGN_KEY_CHECKS=0;
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
-- Déchargement des données de la table `controle`
--

INSERT INTO `controle` (`id`, `created_at`, `value`) VALUES
(139782, '2019-09-17 17:25:00', 'controle_ecumeur'),
(139783, '2019-09-17 17:25:00', 'controle_osmolateur'),
(139784, '2019-09-17 17:25:00', 'controle_bailling'),
(139786, '2019-09-17 17:25:04', 'controle_reacteur'),
(139796, '2019-11-12 23:28:07', 'controle_temperature');

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
  `value` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `state`
--

INSERT INTO `state` (`id`, `created_at`, `path`, `value`) VALUES
(881, '2019-09-15 21:02:21', 'ecumeur', '1'),
(883, '2019-09-17 16:56:33', 'bailling', '111');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `controle`
--
ALTER TABLE `controle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139797;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=884;

--
-- AUTO_INCREMENT pour la table `temperature`
--
ALTER TABLE `temperature`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
