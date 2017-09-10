-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Sep 2017 um 12:20
-- Server-Version: 5.6.25
-- PHP-Version: 7.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `crud`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `api_key`
--

CREATE TABLE IF NOT EXISTS `api_key` (
  `id` int(255) NOT NULL,
  `apikey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `api_key`
--

INSERT INTO `api_key` (`id`, `apikey`, `name`) VALUES
(1, '182b2cdb500ff44b08e61246b41eb6f6883bcb911544b58f4628e6518e1f69ef', 'CRUD Webapp');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `faq`
--

CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(255) NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`) VALUES
(1, 'I wonder, why this is...?', '<p>This is an answer which can also include html</p>'),
(2, 'I wonder, why this is...?', '<p>This is an answer which can also include html</p>'),
(3, 'I wonder, why this is...?', '<p>This is an answer which can also include html</p>'),
(4, 'I wonder, why this is...?', '<p>This is an answer which can also include html</p>'),
(5, 'I wonder, why this is...?', '<p>This is an answer which can also include html</p>');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(255) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirm_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmed` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_plain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_tmp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `email`, `firstname`, `lastname`, `confirm_code`, `confirmed`, `created`, `edited`, `password`, `password_plain`, `password_tmp`) VALUES
(13, 'mail@ca-dsgn.com', 'Christian', 'Albert', '', 'false', '2017-09-05 07:29:45', '0000-00-00 00:00:00', 'eb6443c71a5533bae01238cc4aa3cfec03c3663c9ecb66fd4dc4886490398f60', 'tester', ''),
(16, 'mail@test.de', 'Max', 'Mustermann', '', 'false', '2017-09-10 10:09:27', '0000-00-00 00:00:00', 'eb6443c71a5533bae01238cc4aa3cfec03c3663c9ecb66fd4dc4886490398f60', 'tester', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_sessions`
--

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL,
  `user` int(255) NOT NULL,
  `series` int(11) unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `persistent` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user`, `series`, `token`, `email`, `persistent`, `updated_at`) VALUES
(1, 13, 992882695, '07bd7e725e347b0e060678a1544c1f3f0e3cc67ceab8d40f87e4d15e893a6029', 'mail@ca-dsgn.com', 0, '2017-09-10 09:07:01'),
(2, 13, 2474028873, '0afe7b7cab0b77e941c7a19d419fd07cdb2af93b5cc5b288e71fbdd6d241766c', 'mail@ca-dsgn.com', 0, '2017-09-10 09:12:23'),
(3, 13, 3580634220, 'b26ebfcf835d916468d14906384ed61b78c313dcfa8b339cc6f130142b972d38', 'mail@ca-dsgn.com', 0, '2017-09-10 09:17:22'),
(4, 13, 3907982255, '18cf76523c224180097ac22702f685dc56e8b81748b0001aae68130c43311487', 'mail@ca-dsgn.com', 0, '2017-09-10 09:17:32'),
(5, 13, 2582963694, '077a613754a0a2c7a4a50f26d5f8616847fcb52a1ef6ccb26e4ec7e279e0c232', 'mail@ca-dsgn.com', 0, '2017-09-10 09:25:40'),
(6, 13, 3488791522, '2046ae08698deb1572fd58472385838b0699b083485c04cd518c45e6a0e26027', 'mail@ca-dsgn.com', 0, '2017-09-10 09:26:05'),
(7, 13, 34169856, '26bef8cbe6450e95103498c2fa24a07e7a2a31b398ce18a4283531a9582c2e52', 'mail@ca-dsgn.com', 0, '2017-09-10 09:27:55'),
(8, 13, 1771101892, '08cd04673e489301d587fc8d64dab6ad258d322071190d4be6140ebaeb217edc', 'mail@ca-dsgn.com', 0, '2017-09-10 09:28:21'),
(9, 13, 857249971, 'b3c25b576f58ec7ec37d5afd846b53569ab72fbc9fdfb12943d784c741ec588e', 'mail@ca-dsgn.com', 0, '2017-09-10 09:28:49'),
(10, 13, 1917548511, '2b035b9a8ae6369a6bbca72f68b51db927640e99a9506d4b392d9c330f51c433', 'mail@ca-dsgn.com', 0, '2017-09-10 09:29:46'),
(11, 13, 2693286316, '1d55fb881db805c009b0e35f5cb4bebdd987061ebcbdd14fc8fe0e067242da49', 'mail@ca-dsgn.com', 0, '2017-09-10 09:47:00');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `api_key`
--
ALTER TABLE `api_key`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`,`series`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `api_key`
--
ALTER TABLE `api_key`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT für Tabelle `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
