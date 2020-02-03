-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Lun 03 Février 2020 à 14:05
-- Version du serveur :  5.7.28-0ubuntu0.18.04.4
-- Version de PHP :  7.2.24-0ubuntu0.18.04.2

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
-- Structure de la table `data_parametres_eau`
--

CREATE TABLE `data_parametres_eau` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(10) CHARACTER SET utf8 NOT NULL,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `data_parametres_eau`
--

INSERT INTO `data_parametres_eau` (`id`, `created_at`, `type`, `value`) VALUES
(1, '2020-01-30 00:00:00', 'kh', 7.7),
(2, '2020-02-02 00:00:00', 'kh', 7.3),
(3, '2020-01-30 00:00:00', 'ca', 425),
(4, '2020-02-02 00:00:00', 'ca', 420),
(5, '2020-01-30 00:00:00', 'mg', 1455),
(6, '2020-02-02 00:00:00', 'mg', 1455),
(7, '2020-01-30 00:00:00', 'densite', 1023),
(8, '2020-02-02 00:00:00', 'densite', 1022);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `data_parametres_eau`
--
ALTER TABLE `data_parametres_eau`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `data_parametres_eau`
--
ALTER TABLE `data_parametres_eau`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
