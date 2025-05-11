-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 12:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `product_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `description`) VALUES
(3, 'adsf', 'asdfasdfasdf');

-- --------------------------------------------------------

--
-- Table structure for table `remembered_logins`
--

CREATE TABLE `remembered_logins` (
  `token_hash` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `remembered_logins`
--

INSERT INTO `remembered_logins` (`token_hash`, `user_id`, `expires_at`) VALUES
('b36ae646ddf0942e79b6a4df5e72e74fe0464f077038f8180ff4b2a0b416284b', 163, '2025-08-04 17:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `search`
--

CREATE TABLE `search` (
  `id` int(11) NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` varchar(10000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `url` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `keywords` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `search`
--

INSERT INTO `search` (`id`, `title`, `description`, `url`, `keywords`) VALUES
(2, 'Agile Overview ', 'An overview of the different types of agile.                                                                ', ' /home/agile', 'agile overview types  '),
(78, 'DevOps Books', 'DevOps Books', '/devops/books', 'devops books'),
(79, 'Scrum Books', 'Agile Books                                ', ' /agile/books ', 'Scrum Books'),
(80, 'Roadmap', 'Roadmap', '/scrum/scrumtemplates', 'Roadmap'),
(81, 'Improving Scrum ', 'Improving Scrum ', '/scrumrca', 'Improving Scrum Root Cause Analysis Dysfunctions'),
(82, 'Itil Books', 'Itil Books', '/itil/books', 'ITIL Books Manuals'),
(83, 'Agile Books', 'Agile Books', '  /agile/books', 'agile books'),
(84, 'PHP Books', 'PHP Books', '/php/index', 'php books'),
(85, 'SAFe', 'SAFe', '/safe/index', 'SAFe'),
(87, 'Images Directory Listing as Table', 'Images Directory Listing as Table', '/imagesdirtable.php', 'images directory listing table'),
(88, 'Software in the head', 'Software in the head:  Cartoon about different software methods', '/home/mars', 'soft software mars '),
(89, 'Using DRY Between Code Duplication and High-Coupling', 'Using DRY Between Code Duplication and High-Coupling', '/home/dry', 'Using DRY Between Code Duplication High-Coupling'),
(90, 'Images Directory With Pagination', 'Images Directory With Pagination', '/imagesdir.php', 'images pagination'),
(91, 'Dumbbell exercises video', 'Dumbbell exercises video', '/homeviewvideo/resources/dumbbell.mp4', 'Dumbbell exercises video'),
(92, 'Army exercises', 'Army exercises', '/homeviewpdf/resources/usarmy.pdf', 'Army exercises'),
(93, 'Exercises stretching', 'Exercises stretching', '/homeviewpdf/resources/stretch.pdf', 'Exercises stretching'),
(94, 'Wisdom words', 'Wisdom words', '/booksviewpdf/resources/wordsaday.pdf', 'Wisdom words'),
(95, 'Azure books', 'Azure books', '/azure/books', 'Azure books'),
(96, 'Ubuntu', 'Ubuntu', '/home/ubuntu', 'ubuntu '),
(97, 'PHP Books ', 'PHP Books ', '/php/books', 'PHP Books'),
(98, 'PHP OOP India Course', 'PHP OOP India Course', '/php/indiaoop', 'php crud india'),
(99, 'php objects crud', 'php objects crud', '/oopcrud/View.php', 'php objects crud'),
(100, 'clean code', 'clean code', '/home/cleancode', '/home/cleancode'),
(101, 'Scientology Booklets', 'Scientology Booklets', '/booklets/indexb', 'Scientology Booklets'),
(102, 'Scrum Foundation Course (Able Vids)', 'Scrum Foundation Course (Able Vids)', '/scrum/sfindex', 'Scrum Foundation Course (Able Vids)'),
(103, 'Scrum Master Course (Able Vids)', 'Scrum Master Course (Able Vids)', '/scrum/smindex', 'Scrum Master Course (Able Vids)'),
(104, 'Scrum Product Owner Course (Able Vids)', 'Scrum Product Owner Course (Able Vids)', '/scrum/spoindex', 'Scrum Product Owner Course (Able Vids)'),
(105, 'up', 'up', '/imgman/upload.php', 'upload'),
(106, 'Study Methods and Procrastination ', 'Study Methods and Procrastination ', '/home/procrastination', 'Study Learn Procrastination '),
(107, 'Study Methods ', 'Study methods and techniques.', '/studymethods', 'study learn method'),
(108, '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_reset_hash` varchar(64) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(11) NOT NULL DEFAULT 0,
  `is_access` tinyint(11) NOT NULL DEFAULT 0,
  `activation_hash` varchar(64) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `memstart` date NOT NULL DEFAULT current_timestamp(),
  `memfin` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstname`, `name`, `email`, `password_hash`, `password_reset_hash`, `password_reset_expires_at`, `is_active`, `is_access`, `activation_hash`, `is_admin`, `memstart`, `memfin`) VALUES
(45, '', 'Markus Levy', 'markjc@mweb.co.za', '$2y$10$UShVQSRCTN9s2zSzLxFgDOgrsHfCQvbKs6U2T4z6i1zf93d7wBvdm', NULL, NULL, 1, 0, NULL, 1, '0000-00-00', '0000-00-00'),
(50, '', 'Joe Bloggs', 'JO@JO.co.za', '$2y$10$RHiMbNAIfH098wLUAnlsFOjldXpWd8gVTmhcT7dnT3MDCrDDjTkX.', NULL, NULL, 1, 0, 'dab59f17ffc1c0504e80d5e832f07c548bef3e6924e9a96eea787d7dac9b2023', 0, '2025-05-10', '2025-05-10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `remembered_logins`
--
ALTER TABLE `remembered_logins`
  ADD PRIMARY KEY (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `search`
--
ALTER TABLE `search`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password_reset_hash` (`password_reset_hash`),
  ADD UNIQUE KEY `activation_hash` (`activation_hash`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `search`
--
ALTER TABLE `search`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
