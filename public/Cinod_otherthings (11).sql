-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 26, 2021 at 01:54 PM
-- Server version: 10.2.36-MariaDB
-- PHP Version: 7.1.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Cinod_otherthings`
--

-- --------------------------------------------------------

--
-- Table structure for table `affaires`
--

CREATE TABLE `affaires` (
  `id` int(10) UNSIGNED NOT NULL,
  `all_cables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `note_technician` text DEFAULT NULL,
  `note_master` text DEFAULT NULL,
  `technician` varchar(30) NOT NULL,
  `technician_id` int(3) DEFAULT NULL,
  `affaire_name` varchar(30) NOT NULL,
  `affaire_ref` varchar(50) DEFAULT NULL,
  `date_prepa` date DEFAULT NULL,
  `morning_afternoon_prepa` tinyint(1) DEFAULT NULL,
  `date_sortie` date DEFAULT NULL,
  `morning_afternoon_sortie` tinyint(1) DEFAULT NULL,
  `date_retour` date DEFAULT NULL,
  `morning_afternoon_retour` tinyint(1) DEFAULT NULL,
  `update_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `front` tinyint(1) DEFAULT NULL,
  `monitor` tinyint(1) DEFAULT NULL,
  `stage` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `affaires`
--

INSERT INTO `affaires` (`id`, `all_cables`, `note_technician`, `note_master`, `technician`, `technician_id`, `affaire_name`, `affaire_ref`, `date_prepa`, `morning_afternoon_prepa`, `date_sortie`, `morning_afternoon_sortie`, `date_retour`, `morning_afternoon_retour`, `update_at`, `front`, `monitor`, `stage`) VALUES
(3, 'null', 'This should be seen by master and technician but only technician could edit it', 'This should be seen by master and technician but only master could edit it', 'Yohan', NULL, 'Theatre ', NULL, '2021-01-13', NULL, '2021-01-18', NULL, '2021-01-12', NULL, '2021-01-18 19:42:52', 1, NULL, NULL),
(4, 'null', NULL, NULL, 'Francky', NULL, 'Casino de Paris', NULL, NULL, NULL, '2021-02-17', NULL, '2021-02-25', NULL, '2021-01-13 17:23:19', 1, 1, NULL),
(5, 'null', NULL, NULL, 'Edward', NULL, 'C Cabrel Olympia', NULL, NULL, NULL, '2021-01-19', NULL, '2021-01-20', NULL, '2021-01-13 17:23:29', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cable`
--

CREATE TABLE `cable` (
  `cableid` int(10) UNSIGNED NOT NULL,
  `name` varchar(25) NOT NULL,
  `type` enum('electrical','speaker','microphone') DEFAULT NULL,
  `total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reserved` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ordered` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `info` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cable`
--

INSERT INTO `cable` (`cableid`, `name`, `type`, `total`, `reserved`, `ordered`, `info`, `link`, `timestamp`) VALUES
(1, 'do07', NULL, 80, 10, 0, NULL, NULL, '2021-01-22 15:31:58'),
(2, 'do7', NULL, 60, 10, 0, NULL, NULL, '2021-01-22 15:31:58'),
(3, 'do10', NULL, 50, 10, 0, NULL, NULL, '2021-01-22 15:31:58'),
(4, 'do20', NULL, 35, 10, 0, NULL, NULL, '2021-01-22 15:31:58'),
(5, 'do25', NULL, 25, 8, 0, NULL, NULL, '2021-01-22 15:31:58'),
(6, 'do15p', NULL, 20, 8, 0, NULL, NULL, '2021-01-22 15:31:58'),
(7, 'do10p', NULL, 12, 8, 0, NULL, NULL, '2021-01-22 15:31:58'),
(8, 'dosub sans bague', NULL, 12, 8, 0, NULL, NULL, '2021-01-22 15:31:58'),
(9, 'dosub avec bague', NULL, 8, 2, 0, NULL, NULL, '2021-01-22 15:31:58'),
(10, 'dofill sans bague', NULL, 10, 2, 0, NULL, NULL, '2021-01-22 15:31:58'),
(11, 'dofill avec bague', NULL, 12, 3, 0, NULL, NULL, '2021-01-22 15:31:58'),
(12, 'boitier Do Sp', NULL, 5, 0, 0, NULL, NULL, '2021-01-22 15:31:58');

-- --------------------------------------------------------

--
-- Table structure for table `cables_master`
--

CREATE TABLE `cables_master` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `total` int(3) NOT NULL,
  `tampon` int(3) DEFAULT NULL,
  `typ_cables` int(2) NOT NULL,
  `number_cable` int(3) NOT NULL,
  `info` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cables_master`
--

INSERT INTO `cables_master` (`id`, `name`, `total`, `tampon`, `typ_cables`, `number_cable`, `info`, `link`) VALUES
(1, 'do07', 80, 10, 0, 0, '', ''),
(2, 'do7', 60, 10, 0, 0, '', ''),
(3, 'do10', 50, 10, 0, 0, '', ''),
(4, 'do20', 35, 10, 0, 0, '', ''),
(5, 'do25', 25, 8, 0, 0, '', ''),
(6, 'do15p', 20, 8, 0, 0, '', ''),
(7, 'do10p', 12, 8, 0, 0, '', ''),
(8, 'dosub sans bague', 12, 8, 0, 0, '', ''),
(9, 'dosub avec bague', 8, 2, 0, 0, '', ''),
(10, 'dofill sans bague', 10, 2, 0, 0, '', ''),
(11, 'dofill avec bague', 12, 3, 0, 0, '', ''),
(12, 'boitier Do Sp', 5, NULL, 0, 0, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `cables_used`
--

CREATE TABLE `cables_used` (
  `id` int(11) NOT NULL,
  `nb` int(3) DEFAULT NULL,
  `sec` int(3) DEFAULT NULL,
  `ok` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cables_used`
--

INSERT INTO `cables_used` (`id`, `nb`, `sec`, `ok`) VALUES
(1, NULL, NULL, NULL),
(2, NULL, NULL, NULL),
(3, NULL, NULL, NULL),
(4, NULL, NULL, NULL),
(5, NULL, NULL, NULL),
(6, NULL, NULL, NULL),
(7, NULL, NULL, NULL),
(8, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enceintes`
--

CREATE TABLE `enceintes` (
  `id` int(100) NOT NULL,
  `typ` char(20) NOT NULL,
  `bp` varchar(15) NOT NULL,
  `poids` varchar(10) NOT NULL,
  `ouverture` varchar(20) NOT NULL,
  `spl` varchar(20) NOT NULL,
  `LA4` varchar(10) NOT NULL,
  `LA4_s` varchar(11) NOT NULL,
  `LA4X` varchar(10) NOT NULL,
  `LA4X_s` varchar(11) NOT NULL,
  `LA8` varchar(10) NOT NULL,
  `LA8_s` varchar(11) NOT NULL,
  `LA12X` varchar(10) NOT NULL,
  `LA12X_s` varchar(11) NOT NULL,
  `LA2Xise` varchar(11) NOT NULL,
  `LA2Xise_s` varchar(11) NOT NULL,
  `LA2Xibtl` varchar(11) NOT NULL,
  `LA2Xibtl_s` varchar(11) NOT NULL,
  `remarques` varchar(256) DEFAULT NULL,
  `actif` varchar(20) NOT NULL,
  `voies` int(1) NOT NULL,
  `serie` varchar(50) DEFAULT NULL,
  `link` varchar(100) DEFAULT NULL,
  `picture` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `enceintes`
--

INSERT INTO `enceintes` (`id`, `typ`, `bp`, `poids`, `ouverture`, `spl`, `LA4`, `LA4_s`, `LA4X`, `LA4X_s`, `LA8`, `LA8_s`, `LA12X`, `LA12X_s`, `LA2Xise`, `LA2Xise_s`, `LA2Xibtl`, `LA2Xibtl_s`, `remarques`, `actif`, `voies`, `serie`, `link`, `picture`) VALUES
(1, 'X4i', '120 Hz - 20 kHz', '1 kg', '110°', '116', '16', '4', '16', '4', '24', '6', '24', '6', '16', '4', '--', '--', '', 'Passif', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2019/02/x4i_sps_en_1-1.pdf', 'L-Acoustics_X4i.jpg'),
(2, '5XT', '95 Hz - 20 kHz', '3.5 kg', '110°', '121', '12', '3', '16', '4', '24', '6', '24', '6', '16', '4', '--', '--', '', 'Passif', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/03/5xt_sps_fr_3-1.pdf', ''),
(3, 'X8', '60 Hz - 20 kHz', '12 kg', '100°', '129', '--', '--', '8', '2', '8*', '3', '12', '3', '8', '2', '2', '1', 'LA8 peut piloter jusqu\'à trois X8 par sortie, mais pas plus de huit par contrôleur à haut niveau.', 'Passif', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/x8_sps_fr_3-0-1.pdf', ''),
(4, 'X12', '59 Hz - 20 kHz', '20 kg', '60°-90°', '136', '--', '--', '4', '1', '8', '2', '12', '3', '4', '1', '2', '1', '[X12_MO] 57 Hz - 20 kHz', 'Passif', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/x12_sps_fr_3-0-1.pdf', ''),
(5, 'X15HIQ', '50 Hz - 20 kHz', '21 kg', '40°- 60°', '138', '--', '--', '2', '1', '4', '2', '6', '3', '2', '1', '--', '--', '[X15_MO] 52 Hz - 20 kHz ', 'Actif 2 ch', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/x15-hiq_sps_fr_3-0.pdf', ''),
(6, '8XT', '65 Hz - 20kHz', '11 kg', '100°', '127', '8', '2', '8', '2', '12', '3', '12', '3', '--', '--', '--', '--', '', 'Passif', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_xt_sps_en_5-1.pdf', ''),
(7, '12XT actif', '55 Hz - 20kHz', '', '90°', '0', '4', '2', '4', '2', '6', '3', '6', '3', '--', '--', '--', '--', '', 'Actif 2 ch', 2, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_12xt_sp_en_4-2.pdf', ''),
(8, '12XT passif', '55 Hz - 20kHz', '', '90°', '0', '4', '1', '4', '1', '8', '2', '12', '3', '--', '--', '--', '--', NULL, 'Actif 2 ch', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_12xt_sp_en_4-2.pdf', ''),
(9, '112XT', '65 Hz - 18kHz', '', '90°', '124', '4', '2', '4', '2', '6', '3', '6', '3', '--', '--', '--', '--', NULL, 'Passif', 1, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_112xtgb.pdf', ''),
(10, '115XTHIQ', '50Hz - 20kHz', '', '50°', '139.5 MO - 136.5 FI', '2', '1', '2', '1', '4', '2', '6', '3', '--', '--', '--', '--', NULL, 'Actif 2ch', 2, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_hiq_sp_en_4-2.pdf', ''),
(11, '115XT', '60 Hz - 18kHz', '', '80°', '124', '2', '1', '2', '1', '6', '3', '6', '3', '--', '--', '--', '--', NULL, 'Actif 2 ch', 2, 'X', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_115xtgb.pdf', ''),
(12, 'MTD108a', '85 HZ - 20 kHz', '', '', '0', '8', '2', '8', '2', '12', '3', '12', '3', '--', '--', '--', '--', NULL, 'Passif', 1, 'MTD', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_mtd108agb.pdf', ''),
(13, 'MTD112b', '70 Hz - 14 kHz', '', '', '0', '4', '1', '4', '1', '8', '2', '8', '2', '--', '--', '--', '--', NULL, 'Passif', 1, 'MTD', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_mtd112bgb.pdf', ''),
(14, 'MTD115b A', '65 Hz - 14 kHz', '', '', '0', '2', '1', '2', '1', '4', '2', '4', '2', '--', '--', '--', '--', NULL, 'Actif 2 ch', 2, 'MTD', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_mtd115bgb.pdf', ''),
(15, 'MTD115b-p', '70 Hz - 14 kHz', '', '', '0', '4', '1', '4', '1', '8', '2', '8', '2', '--', '--', '--', '--', NULL, 'Passif', 1, 'MTD', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_mtd115bgb.pdf', ''),
(17, 'ARC Wide/Focus', '55 Hz - 20 kHz', '', '', '0', '4', '1', '4', '1', '8', '2', '12', '3', '4', '1', '2', '1', NULL, 'Passif', 1, 'A', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/arcswifo_sps_fr_3-0.pdf', ''),
(18, 'A10 Wide/Focus', '67 Hz - 20 kHz', '20 kg', '', '0', '--', '--', '8', '2', '8', '2', '12', '3', '8', '2', '2', '1', NULL, 'Passif 2 voies', 2, 'A', 'https://www.l-acoustics.com/wp-content/uploads/2020/02/a10_sps_fr_1-2.pdf', ''),
(19, 'A15 Wide/Focus', '41 Hz - 20 kHz', '33 kg', '', '0', '--', '--', '4', '1', '8', '2', '12', '3', '4', '1', '2', '1', NULL, 'Passif 2 voies', 2, 'A', 'https://www.l-acoustics.com/wp-content/uploads/2019/05/a15_sps_en_1-2.pdf', ''),
(20, 'ARCII', '50 Hz - 20 kHz', '', '', '0', '--', '--', '2', '1', '4', '2', '6', '3', '--', '--', '--', '--', NULL, 'Actif 2 voies', 2, 'A', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/arcsii_sps_fr_3-0.pdf', ''),
(21, 'ARCS', '50 Hz - 20 kHz', '', '', '0', '2', '1', '2', '1', '6', '3', '6', '3', '--', '--', '', '', NULL, 'Actif 2 voies', 2, 'A', NULL, ''),
(22, 'K1', '35 Hz - 20 kHz', '', '', '0', '--', '--', '--', '--', '2', '2', '2', '2', '--', '--', '--', '--', NULL, 'Actif 3 voies', 3, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2018/03/k1_rm_fr.pdf', ''),
(23, 'K1-SB', '30 Hz', '', '', '0', '--', '--', '--', '--', '4', '1', '4', '1', '--', '--', '--', '--', NULL, 'Passif', 1, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/k1sb_sp_fr_2-0.pdf', ''),
(24, 'K2', '35 Hz - 20 kHz', '', '', '0', '--', '--', '1', '1', '3', '3', '3', '3', '--', '--', '--', '--', NULL, 'Actif 3 voies', 3, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/k2_sps_fr_1-2.pdf', ''),
(25, 'KARA II', '55 Hz - 20 kHz', '', '', '0', '--', '--', '4', '2', '6', '3', '6', '3', '4', '2', '--', '--', NULL, 'Actif 2 voies', 2, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2020/01/kara_ii_sps_en_1-1.pdf', ''),
(26, 'KivaII', '70 Hz - 20 kHz', '', '', '0', '--', '--', '8', '2', '16', '4', '24', '6', '8', '2', '4', '2', NULL, 'Passif', 1, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2018/03/kivaii_sps_fr_3-1.pdf', ''),
(28, 'Kudo', '35 Hz - 20 kHz', '', '', '0', '--', '--', '1', '1', '3', '3', '3', '3', '--', '--', '--', '--', '', 'Actif 3 voies', 3, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_kudo_sp_en_5-0.pdf', ''),
(29, 'V-DOSC', '40 Hz - 20 kHz', '', '', '0', '--', '--', '--', '--', '2', '2', '2', '2', '--', '--', '--', '--', NULL, 'Actif 3 voies', 3, 'V', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_vdosc_sp_en_1-0-1.pdf', ''),
(30, 'dV-DOSC', '65 Hz - 20 kHz', '', '', '0', '--', '--', '--', '--', '6', '3', '6', '3', '--', '--', '--', '--', NULL, 'Actif 2 voies', 2, 'V', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_dv_sps_en_1-0.pdf', ''),
(27, 'Kiva / Kilo', '80 Hz - 20 kHz', '', '', '0', '8', '2', '8', '2', '12', '3', '12', '3', '--', '--', '--', '--', NULL, 'Passif', 1, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_kilo_sp_en_4-1.pdf', ''),
(31, 'KS28', '25 Hz', '79 kg', '', '0', '--', '--', '--', '--', '--', '--', '4', '1', '4', '1', '--', '--', NULL, 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/ks28_sps_fr_2-0.pdf', ''),
(32, 'SB28', '25 Hz', '93 kg', '', '0', '--', '--', '--', '--', '4', '1', '4', '1', '4', '1', '--', '--', NULL, 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/sb28_sp_fr_6-0.pdf', ''),
(33, 'KS21', '29 Hz', '49 kg', '', '0', '--', '--', '4', '1', '6*', '2', '8', '2', '4', '1', '2', '1', 'LA8 peut piloter jusqu\'à deux KS21 par sortie, mais pas plus de six par contrôleur à haut niveau. BP  29 Hz à 60 Hz s’il\r\nest associé avec le système A15, ou de 30 Hz à 100 Hz s’il est associé\r\navec le système A10', 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2020/02/ks21_sps_fr_1-2-1.pdf', ''),
(34, 'SB18', '32Hz', '52 kg', '', '0', '4', '1', '4', '1', '8', '2', '12', '3', '4', '1', '2', '1', NULL, 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2018/06/sb18_sp_fr_6-0.pdf\r\n', ''),
(35, 'SB218', '28 Hz', '106 kg', '', '0', '--', '--', '--', '--', '4', '1', '4', '1', '--', '--', '--', '--', NULL, 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_sb218gb.pdf', ''),
(36, 'SB118', '32 Hz', '61 kg', '', '0', '4', '1', '4', '1', '8', '2', '8', '2', '--', '--', '--', '--', NULL, 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/en_sb118_sp_en_4-0.pdf', ''),
(37, 'SB15m', '40 Hz', '36 kg', '', '0', '4', '1', '4', '1', '6*', '2', '12', '3', '4', '1', '2', '1', 'LA8 peut piloter jusqu\'à deux SB15m par sortie, mais pas plus de six par contrôleur à haut niveau.', 'Passif', 1, 'SUB', 'https://www.l-acoustics.com/wp-content/uploads/2019/03/sb15m_sp_fr_2-1.pdf', ''),
(38, 'Syva Low', '40 Hz', '29 kg', '', '0', '--', '--', '4', '1', '4', '1', '6*', '2', '4', '1', '--', '--', 'LA12X peut piloter jusqu\'à deux Syva Low par sortie, mais pas plus de six par contrôleur à haut niveau.', 'Passif', 1, 'SY', 'https://www.l-acoustics.com/wp-content/uploads/2019/08/syva_sps_fr_2-0.pdf', ''),
(39, 'Syva Sub', '27 Hz', '27 kg', '', '0', '4', '1', '4', '1', '8', '2', '12', '3', '4', '1', '2', '1', NULL, 'Passif', 1, 'SY SUB', 'https://www.l-acoustics.com/wp-content/uploads/2018/02/syva_um_en.pdf', ''),
(40, 'dV-SUB', '35 Hz', '93 kg', '', '0', '--', '--', '--', '--', '4', '1', '4', '1', '--', '--', '--', '--', NULL, 'Passif', 1, 'SUB V', 'https://www.l-acoustics.com/wp-content/uploads/2018/10/dv_sps_en_1-0.pdf', ''),
(16, 'Syva', '87 Hz - 20 kHz', '', '', '0', '--', '--', '4', '1', '8', '2', '12', '3', '4', '1', '2', '1', NULL, 'Passif', 1, 'SY', 'https://www.l-acoustics.com/wp-content/uploads/2018/02/syva_um_en.pdf', ''),
(41, 'K3', '42 Hz - 20 kHz', '43 kg', '', '0', '--', '--', '2', '1', '4', '2', '6', '3', '--', '--', '--', '--', NULL, 'actif 2 voies', 2, 'K', 'https://www.l-acoustics.com/wp-content/uploads/2020/10/k3_sps_en_1-0.pdf', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `affaires`
--
ALTER TABLE `affaires`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cable`
--
ALTER TABLE `cable`
  ADD PRIMARY KEY (`cableid`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `cables_master`
--
ALTER TABLE `cables_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cables_used`
--
ALTER TABLE `cables_used`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enceintes`
--
ALTER TABLE `enceintes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `affaires`
--
ALTER TABLE `affaires`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cable`
--
ALTER TABLE `cable`
  MODIFY `cableid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cables_master`
--
ALTER TABLE `cables_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cables_used`
--
ALTER TABLE `cables_used`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enceintes`
--
ALTER TABLE `enceintes`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
