-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 04:16 PM
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
-- Database: `pcyouconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminlogincredentials`
--

CREATE TABLE `adminlogincredentials` (
  `AdminID` int(11) DEFAULT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminlogincredentials`
--

INSERT INTO `adminlogincredentials` (`AdminID`, `FirstName`, `LastName`, `Password`) VALUES
(2026, 'admin', 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `studentlogincredentials`
--

CREATE TABLE `studentlogincredentials` (
  `StudentID` int(11) NOT NULL,
  `FirstName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `studentlogincredentials`
--

INSERT INTO `studentlogincredentials` (`StudentID`, `FirstName`, `LastName`, `Password`) VALUES
(123456, 'admin', 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` enum('important','event','maintenance','general') NOT NULL DEFAULT 'general',
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_announcements_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lost_found`
--

CREATE TABLE `lost_found` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('lost','found') NOT NULL DEFAULT 'found',
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Others',
  `description` varchar(1000) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('unclaimed','claimed','turned in') NOT NULL DEFAULT 'unclaimed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lost_found_created_at` (`created_at`),
  KEY `idx_lost_found_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `studentlogincredentials`
--
ALTER TABLE `studentlogincredentials`
  ADD PRIMARY KEY (`StudentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
