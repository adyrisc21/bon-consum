-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Gazdă: localhost:3306
-- Timp de generare: apr. 26, 2026 la 10:24 PM
-- Versiune server: 8.0.37
-- Versiune PHP: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Bază de date: `adyrisce_bon`
--

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `bonuri`
--

CREATE TABLE `bonuri` (
  `id` int NOT NULL,
  `unitatea` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'METROREX SA',
  `numar_document` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `zi` varchar(5) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `luna` varchar(5) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `an` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `produs_lucrare` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `predator` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `primitor` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `cod_gestiune` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `sef_compartiment` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `gestionar` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `primitor_semnatura` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `bonuri_pozitii`
--

CREATE TABLE `bonuri_pozitii` (
  `id` int NOT NULL,
  `bon_id` int NOT NULL,
  `produs_id` int DEFAULT NULL,
  `nr_crt` int NOT NULL,
  `denumire` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `cantitate_necesara` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `cod` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `um` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `cantitate_eliberata` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `pret_unitar` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `valoarea` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `miscari_stoc`
--

CREATE TABLE `miscari_stoc` (
  `id` int NOT NULL,
  `produs_id` int NOT NULL,
  `tip_miscare` enum('intrare','iesire') COLLATE utf8mb3_unicode_ci NOT NULL,
  `cantitate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pret_unitar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stoc_dupa_miscare` decimal(12,2) NOT NULL DEFAULT '0.00',
  `document_tip` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `document_id` int DEFAULT NULL,
  `document_nr` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `data_miscare` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observatii` text COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `produse`
--

CREATE TABLE `produse` (
  `id` int NOT NULL,
  `denumire` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `cod_produs` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `cod_gestiune` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `um` varchar(30) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `pret_unitar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stoc_curent` decimal(12,2) NOT NULL DEFAULT '0.00',
  `data_intrare` date DEFAULT NULL,
  `furnizor` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT '',
  `observatii` text COLLATE utf8mb3_unicode_ci,
  `activ` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Eliminarea datelor din tabel `produse`
--

INSERT INTO `produse` (`id`, `denumire`, `cod_produs`, `cod_gestiune`, `um`, `pret_unitar`, `stoc_curent`, `data_intrare`, `furnizor`, `observatii`, `activ`, `created_at`, `updated_at`) VALUES
(8, 'Intrerupator USOl 100A/32 A', '21.370.0015', '30241', 'buc', 187.28, 1.00, '2026-03-04', 'Magazie Pacii', 'Echipament Vechi', 1, '2026-04-24 08:47:00', '2026-04-26 18:47:09'),
(9, 'Priza imd. fixa 32A', '21.290.0745', '30241', 'buc', 55.92, 1.00, '2026-03-04', 'Magazie Pacii', '', 1, '2026-04-24 08:48:24', '2026-04-24 08:48:24'),
(10, 'Buton Comanda fara retinere', '25.020.1178', '30241', 'buc', 22.32, 7.00, '2026-02-26', 'Magazie Pipera', '', 1, '2026-04-24 08:49:25', '2026-04-24 08:49:25'),
(11, 'Contactor 53E 5ND-3NI 230/220v 50 Hz', '25.010.1125', '30241', 'buc', 135.14, 2.00, '2026-02-12', 'Magazie Pacii', '', 1, '2026-04-24 08:51:07', '2026-04-24 08:51:07'),
(12, 'Contactor Auxiliar DILER-40 230v 50Hz', '25.010.1137', '30241', 'buc', 99.10, 2.00, '2026-04-24', 'Magazir Pacii', '', 1, '2026-04-24 08:52:28', '2026-04-24 08:52:28'),
(13, 'Disjunctor 1P+N 10A 10KA', '21.220.0037', '30241', 'buc', 28.89, 2.00, '2025-11-20', 'Magazie Pacii', '', 1, '2026-04-24 08:54:02', '2026-04-26 18:45:59'),
(14, 'Disjunctor diferential RCBO 1P+N 10A', '21.120.0034', '30241', 'buc', 148.49, 3.00, '2026-11-20', 'Magazie Pacii', '', 1, '2026-04-24 08:55:38', '2026-04-24 08:55:38'),
(15, 'Disjunctor diferential RCBO 1P+N 16A', '21.220.0035', '30241', 'buc', 149.31, 3.00, '2025-11-04', 'Magazie Pacii', '', 1, '2026-04-24 08:57:01', '2026-04-24 08:57:01');

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `bonuri`
--
ALTER TABLE `bonuri`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `bonuri_pozitii`
--
ALTER TABLE `bonuri_pozitii`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bon_id` (`bon_id`),
  ADD KEY `fk_bonuri_pozitii_produs` (`produs_id`);

--
-- Indexuri pentru tabele `miscari_stoc`
--
ALTER TABLE `miscari_stoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_miscari_produs` (`produs_id`);

--
-- Indexuri pentru tabele `produse`
--
ALTER TABLE `produse`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_cod_produs` (`cod_produs`),
  ADD KEY `idx_denumire` (`denumire`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `bonuri`
--
ALTER TABLE `bonuri`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT pentru tabele `bonuri_pozitii`
--
ALTER TABLE `bonuri_pozitii`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT pentru tabele `miscari_stoc`
--
ALTER TABLE `miscari_stoc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pentru tabele `produse`
--
ALTER TABLE `produse`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constrângeri pentru tabele eliminate
--

--
-- Constrângeri pentru tabele `bonuri_pozitii`
--
ALTER TABLE `bonuri_pozitii`
  ADD CONSTRAINT `bonuri_pozitii_ibfk_1` FOREIGN KEY (`bon_id`) REFERENCES `bonuri` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bonuri_pozitii_produs` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE SET NULL;

--
-- Constrângeri pentru tabele `miscari_stoc`
--
ALTER TABLE `miscari_stoc`
  ADD CONSTRAINT `fk_miscari_produs` FOREIGN KEY (`produs_id`) REFERENCES `produse` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
