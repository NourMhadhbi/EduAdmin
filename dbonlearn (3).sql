-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 30, 2025 at 01:27 PM
-- Server version: 5.7.39
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbonlearn`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrateur`
--

CREATE TABLE `administrateur` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cours`
--

CREATE TABLE `cours` (
  `id` int(11) NOT NULL,
  `titre` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `level` enum('debutant','intermediaire','avance') COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enseignant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cours`
--

INSERT INTO `cours` (`id`, `titre`, `description`, `level`, `category`, `icon`, `image`, `enseignant_id`) VALUES
(2, 'Mathématiques générales', 'Cours de math pour débutant', 'intermediaire', 'informatique', NULL, 'lena.jpg', 10),
(3, 'Physique fondamentale', 'Introduction à la mécanique classique', NULL, '', NULL, NULL, 10),
(4, 'Bases de données', 'Apprentissage du SQL et des modèles relationnels', NULL, '', NULL, NULL, 10),
(5, 'Algorithme', 'vvvv', NULL, '', NULL, NULL, 10),
(6, 'Développement Web Full Stack', 'Apprenez à créer des sites web modernes avec HTML, CSS, JavaScript et les frameworks populaires comme React et Node.js.', 'debutant', 'Développement', 'fas fa-code', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1170&q=80', 10),
(7, 'UI/UX Design Principles', 'Maîtrisez les principes fondamentaux du design d\'interface utilisateur et d\'expérience utilisateur avec des projets pratiques.', 'intermediaire', 'Design', 'fas fa-palette', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=1100&q=80', 10),
(8, 'Data Science avec Python', 'Découvrez l\'analyse de données, le machine learning et la visualisation avec Python et ses bibliothèques spécialisées.', 'avance', 'Data Science', 'fas fa-chart-line', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1170&q=80', 10),
(9, 'Marketing Digital', 'Stratégies de marketing en ligne, référencement, publicité sur les réseaux sociaux et analyse de données.', 'debutant', 'Business', 'fas fa-bullhorn', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1115&q=80', 10),
(10, 'Développement Mobile iOS', 'Créez des applications iOS avec Swift et SwiftUI, de la conception à la publication.', 'intermediaire', 'Développement', 'fas fa-mobile-alt', 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=1170&q=80', 10),
(11, 'Gestion de Projet Agile', 'Maîtrisez les méthodologies Agile, Scrum et Kanban pour gérer efficacement vos projets.', 'intermediaire', 'Business', 'fas fa-tasks', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1170&q=80', 10),
(12, 'Cybersécurité Fondamentale', 'Apprenez les bases de la sécurité informatique et protégez vos systèmes contre les menaces courantes.', 'debutant', 'Développement', 'fas fa-shield-alt', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1170&q=80', 10),
(13, 'Intelligence Artificielle', 'Introduction aux concepts de l\'IA, du machine learning et des réseaux neuronaux.', 'avance', 'Data Science', 'fas fa-robot', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1332&q=80', 10),
(14, 'Anglais des Affaires', 'Améliorez votre anglais professionnel pour communiquer efficacement dans un environnement international.', 'debutant', 'Business', 'fas fa-language', 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&w=1170&q=80', 10),
(15, 'Photographie Professionnelle', 'Maîtrisez les techniques de photographie professionnelle pour créer des images percutantes.', 'intermediaire', 'Design', 'fas fa-camera', 'https://images.unsplash.com/photo-1554048612-b6a482bc67e5?auto=format&fit=crop&w=1170&q=80', 10),
(16, 'Blockchain et Cryptomonnaies', 'Comprenez les fondamentaux de la technologie blockchain et son application dans les cryptomonnaies.', 'avance', 'Développement', 'fas fa-coins', 'https://images.unsplash.com/photo-1621570075081-41b5d1616ed3?auto=format&fit=crop&w=1170&q=80', 10),
(17, 'Analyse de Données avec R', 'Apprenez à analyser et visualiser des données avec le langage R.', 'intermediaire', 'Data Science', 'fas fa-chart-bar', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1170&q=80', 10),
(18, 'Management d\'Équipe', 'Développez vos compétences en leadership pour manager efficacement une équipe.', 'intermediaire', 'Business', 'fas fa-users', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1170&q=80', 10),
(20, 'Design Graphique Avancé', 'Perfectionnez vos compétences en design graphique avec des techniques avancées.', 'avance', 'Design', 'fas fa-paint-brush', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=1100&q=80', 10),
(21, 'Développement Jeux Vidéo', 'Créez vos propres jeux vidéo avec Unity et C#, de la conception à la publication.', 'avance', 'Développement', 'fas fa-gamepad', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1170&q=80', 10),
(22, 'Communication d\'Entreprise', 'Améliorez vos compétences en communication pour un environnement professionnel efficace.', 'debutant', 'Business', 'fas fa-comments', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1170&q=80', 10),
(23, 'Mathématiques générales', 'uuu', 'debutant', 'info', NULL, NULL, 10),
(24, 'Intelligence A', 'GestionDesCoursEns', 'avance', 'info', NULL, 'imageProfilTest.jpg', 10),
(25, 'ddddd', 'dddd', 'intermediaire', 'info', NULL, 'patiebtProfil.webp', 10);

-- --------------------------------------------------------

--
-- Table structure for table `enseignant`
--

CREATE TABLE `enseignant` (
  `id` int(11) NOT NULL,
  `matricule` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `specialite` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isActive` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `enseignant`
--

INSERT INTO `enseignant` (`id`, `matricule`, `specialite`, `isActive`) VALUES
(10, '1478963', 'hhhhhh', 0);

-- --------------------------------------------------------

--
-- Table structure for table `etudiant`
--

CREATE TABLE `etudiant` (
  `id` int(11) NOT NULL,
  `matricule` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `isActive` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `etudiant`
--

INSERT INTO `etudiant` (`id`, `matricule`, `isActive`) VALUES
(3, '', 1),
(4, '123456', 1),
(5, '8888888', 1),
(6, '5455488', 0),
(7, '1234564', 0),
(8, '1234567', 0),
(9, '5656456', 0),
(13, '8888546', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inscriptioncours`
--

CREATE TABLE `inscriptioncours` (
  `etudiant_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `inscriptioncours`
--

INSERT INTO `inscriptioncours` (`etudiant_id`, `cours_id`) VALUES
(13, 2),
(13, 3),
(13, 4),
(13, 5),
(13, 14),
(13, 17),
(13, 21),
(13, 22);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `titre` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `icone` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'fa-info-circle',
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `utilisateur_id`, `titre`, `icone`, `message`, `date_creation`, `lu`) VALUES
(1, 13, 'Mise à jour de la photo de profil', 'fa-circle-info', 'Votre photo de référence doit être mise à jour pour améliorer la reconnaissance faciale.', '2025-11-05 15:11:30', 1),
(2, 13, 'Absences non justifiées', 'fa-triangle-exclamation', 'Vous avez 2 absences non justifiées dans le cours d\'Intelligence Artificielle.', '2025-11-04 17:11:30', 1),
(3, 13, 'Nouveau cours ajouté', 'fa-book', 'Le cours de Programmation Web a été ajouté à votre emploi du temps.', '2025-11-02 17:11:30', 1),
(4, 14, 'Rappel de devoir', 'fa-calendar-check', 'Le devoir du module Machine Learning est à rendre avant vendredi.', '2025-10-31 17:11:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `presence`
--

CREATE TABLE `presence` (
  `id` int(11) NOT NULL,
  `etudiant_id` int(11) NOT NULL,
  `seance_id` int(11) NOT NULL,
  `statut` enum('Présent','Absent','EnAttente') COLLATE utf8_unicode_ci NOT NULL,
  `imageCapture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `presence`
--

INSERT INTO `presence` (`id`, `etudiant_id`, `seance_id`, `statut`, `imageCapture`, `created_at`) VALUES
(5, 13, 2, 'Présent', 'probe_6910775980bcf.jpg', '2025-11-11 18:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `seance`
--

CREATE TABLE `seance` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `cours_id` int(11) NOT NULL,
  `heureDebut` time NOT NULL,
  `heureFin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `seance`
--

INSERT INTO `seance` (`id`, `date`, `cours_id`, `heureDebut`, `heureFin`) VALUES
(2, '2025-11-15', 4, '14:00:00', '18:00:00'),
(3, '2025-11-09', 2, '21:52:01', '22:18:01'),
(4, '2025-11-20', 2, '14:00:00', '18:00:00'),
(5, '2025-11-28', 2, '05:19:00', '16:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `systemereconnaissance`
--

CREATE TABLE `systemereconnaissance` (
  `id` int(11) NOT NULL,
  `apiUrl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modeleFacial` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `motDePasse` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `photoProfil` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` enum('Admin','Enseignant','Etudiant') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_code` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `email`, `motDePasse`, `photoProfil`, `role`, `created_at`, `update_at`, `reset_code`, `reset_expire`) VALUES
(1, 'Mhadhbi', 'Hichem', 'mouhamed@gmail.com', '$2y$10$f7JQBs/vY7UOaBh.XqmwgOxz4mOzTC2tXeCOFSMBWt.CKh1jfs9Ry', 'default_profile.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(2, 'Mhadhbi', 'Hichem', 'hichemmhadhbi1976@gmail.com', '$2y$10$Ep2Wfmv3SIKp5Frw6QoQKOLQSOvfxrv3pdMQyBCDQxJOP6bmf6inG', 'default_profile.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(3, 'Mhadhbi', 'Hichem', 'mhadhbi.nour106@gmail.com', '$2y$10$2ZxSvWHkzpqnid8p.Qc2fe5j2JI5WqouVABEqYgOjCiCsh5WJyFeS', 'default_profile.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', '985604', '2025-11-14 20:45:31'),
(4, 'Mhadhbi', 'Hichem', 'mhadhbi.nour16@gmail.com', '$2y$10$8R7z3ZMrrWVTux3XXwlTvu/RdQocHdPvv30sfzMSYJnEZivGRL/pC', 'default_etudiant.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(5, 'Mhadhbi', 'Hichem', 'ahmedAbid@gmail.com', '$2y$10$/qhqp7kjv3voJUVmySg1ZOhOjy/QoEZ8hGYrSgWsck/n0I6/MWf46', 'default_etudiant.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(6, 'Mhadhbi', 'Hichem', 'ahmedAbnid@gmail.com', '$2y$10$FBreiBpNSWSyeh8edhg8C.snl3yC3ND64h5/Od8ub4BH6v7e.yG6y', 'default_etudiant.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(7, 'Mhadhbi', 'Hichem', 'mouhamed21@gmail.com', '$2y$10$d1KvrfPpe.LxfBaxTciRx.chmNA4gRqzK7RnqphvTk3HOoqw.uBcC', 'default_etudiant.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(8, 'Mhadhbi', 'Hichem', 'ahmedAbid1233@gmail.com', '$2y$10$.4XEZWzGND68ICOOABjZcekAxIy4NWO35w1DyjFeA1v5gmVYvrhlK', 'default_etudiant.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(9, 'abid', 'safa', 'safabid995@gmail.com', '$2y$10$mYEKaootpCzvQ.VSKWKr4O4IcyNexCd92P8WAhoZaySH4tPvihRkG', 'default_etudiant.png', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(10, 'Mhadhbi', 'Hichem', 'safabid95@gmail.com', '$2y$10$1vpfTASMvoHP9EDXZX1l0uMfjUXqVaeQuBajua189eQHacED3R6Li', 'default_enseignant.png', 'Enseignant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(13, 'Mhadhbi', 'fadwa', 'fadwa@gmail.com', '$2y$10$A.F7TQChYk0ZzV.s34XLZeZvTiSunnvYkuUzHHoJhjq1KWKGXpjoK', 'lena.jpg', 'Etudiant', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL),
(14, 'mhadhbi', 'mouhamedd', 'mmhadhbi@gmail.com', '$2y$10$H8ZA/SXs1L8muR.l69E4Beccze2SAVkuh.iza3e4RR7M8f5ygGGuy', 'téléchargement.jpeg', 'Admin', '2025-11-10 19:42:34', '2025-11-10 19:42:34', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cours_enseignant` (`enseignant_id`);

--
-- Indexes for table `enseignant`
--
ALTER TABLE `enseignant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_matriculeEns` (`matricule`);

--
-- Indexes for table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricule` (`matricule`);

--
-- Indexes for table `inscriptioncours`
--
ALTER TABLE `inscriptioncours`
  ADD PRIMARY KEY (`etudiant_id`,`cours_id`),
  ADD KEY `fk_inscription_cours` (`cours_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `presence`
--
ALTER TABLE `presence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_presence_etudiant` (`etudiant_id`),
  ADD KEY `fk_presence_seance` (`seance_id`);

--
-- Indexes for table `seance`
--
ALTER TABLE `seance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_seance_cours` (`cours_id`);

--
-- Indexes for table `systemereconnaissance`
--
ALTER TABLE `systemereconnaissance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cours`
--
ALTER TABLE `cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `presence`
--
ALTER TABLE `presence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seance`
--
ALTER TABLE `seance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `systemereconnaissance`
--
ALTER TABLE `systemereconnaissance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrateur`
--
ALTER TABLE `administrateur`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `fk_cours_enseignant` FOREIGN KEY (`enseignant_id`) REFERENCES `enseignant` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enseignant`
--
ALTER TABLE `enseignant`
  ADD CONSTRAINT `fk_enseignant_user` FOREIGN KEY (`id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `etudiant`
--
ALTER TABLE `etudiant`
  ADD CONSTRAINT `fk_etudiant_user` FOREIGN KEY (`id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inscriptioncours`
--
ALTER TABLE `inscriptioncours`
  ADD CONSTRAINT `fk_inscription_cours` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscription_etudiant` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `presence`
--
ALTER TABLE `presence`
  ADD CONSTRAINT `fk_presence_etudiant` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiant` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_presence_seance` FOREIGN KEY (`seance_id`) REFERENCES `seance` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seance`
--
ALTER TABLE `seance`
  ADD CONSTRAINT `fk_seance_cours` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
