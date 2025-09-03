-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 13/08/2025 às 02:48
-- Versão do servidor: 10.11.10-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-03:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Estrutura para tabela `admin_config`
--

CREATE TABLE `admin_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `admin_config`
--

INSERT INTO `admin_config` (`id`, `config_key`, `config_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'win_probability', '0.0', 'Probabilidade de vitória no jogo (0.0 a 1.0)', '2025-07-21 05:55:49', '2025-07-21 06:04:36'),
(2, 'game_enabled', '1', 'Status do jogo (1 = ativo, 0 = inativo)', '2025-07-21 05:55:49', '2025-07-21 05:55:49'),
(3, 'min_bet', '1.00', 'Valor mínimo de aposta', '2025-07-21 06:04:36', '2025-07-21 06:04:36'),
(4, 'max_bet', '100.00', 'Valor máximo de aposta', '2025-07-21 06:04:36', '2025-07-21 06:04:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin_simulated_reports`
--

CREATE TABLE `admin_simulated_reports` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `simulated_clicks` int(11) DEFAULT 0,
  `simulated_conversions` int(11) DEFAULT 0,
  `report_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `admin_simulated_reports`
--

INSERT INTO `admin_simulated_reports` (`id`, `affiliate_id`, `simulated_clicks`, `simulated_conversions`, `report_date`, `created_at`) VALUES
(1, 3, 20, 0, '2025-07-21', '2025-07-21 06:43:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `affiliates`
--

CREATE TABLE `affiliates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `affiliate_code` varchar(255) NOT NULL,
  `cpa_commission_rate` decimal(5,2) DEFAULT 0.00,
  `revshare_commission_rate` decimal(5,2) DEFAULT 0.00,
  `cpa_commission_rate_admin` decimal(5,2) DEFAULT 0.00,
  `revshare_commission_rate_admin` decimal(5,2) DEFAULT 0.00,
  `allow_sub_affiliate_earnings` tinyint(1) DEFAULT 1,
  `fixed_commission_per_signup` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `agent_commission_rate` decimal(10,2) DEFAULT 15.00,
  `agent_id` int(11) DEFAULT NULL,
  `agent_defined_rate` decimal(10,2) DEFAULT NULL,
  `show_deductions` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Exibir descontos para o afiliado (0=oculto, 1=visível)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `affiliates`
--

INSERT INTO `affiliates` (`id`, `user_id`, `affiliate_code`, `cpa_commission_rate`, `revshare_commission_rate`, `cpa_commission_rate_admin`, `revshare_commission_rate_admin`, `allow_sub_affiliate_earnings`, `fixed_commission_per_signup`, `is_active`, `created_at`, `updated_at`, `agent_commission_rate`, `agent_id`, `agent_defined_rate`, `show_deductions`) VALUES
(3, 1, 'admin1', 10.00, 50.00, 0.00, 30.02, 1, 0.00, 1, '2025-07-13 20:52:41', '2025-08-02 06:37:19', 15.00, NULL, NULL, 0),
(44, 146, '1231231231312', 10.00, 50.00, 0.00, 50.00, 1, 0.00, 1, '2025-08-05 02:20:08', '2025-08-05 06:16:57', 15.00, NULL, NULL, 0),
(46, 2, 'sadasdassa2', 10.00, 5.00, 0.00, 0.00, 1, 0.00, 1, '2025-08-09 03:03:02', '2025-08-09 03:03:02', 15.00, NULL, NULL, 0),
(50, 198, 'qweqweqwe198', 10.00, 5.00, 0.00, 0.00, 1, 0.00, 1, '2025-08-13 01:58:43', '2025-08-13 01:59:15', 15.00, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `affiliate_clicks`
--

CREATE TABLE `affiliate_clicks` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `affiliate_clicks`
--

INSERT INTO `affiliate_clicks` (`id`, `affiliate_id`, `ip_address`, `user_agent`, `clicked_at`, `created_at`) VALUES
(9, 38, '2804:1b1:f941:4400:fc49:8981:9f60:56e8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-02 04:29:28', '2025-08-11 17:18:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `affiliate_conversions`
--

CREATE TABLE `affiliate_conversions` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `converted_user_id` int(11) NOT NULL,
  `conversion_type` enum('signup','deposit','sale') NOT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `converted_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `affiliate_conversions`
--

INSERT INTO `affiliate_conversions` (`id`, `affiliate_id`, `converted_user_id`, `conversion_type`, `amount`, `converted_at`, `created_at`) VALUES
(7, 38, 107, 'signup', 0.00, '2025-08-02 04:29:36', '2025-08-11 17:19:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `agent_rate_changes`
--

CREATE TABLE `agent_rate_changes` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `old_rate` decimal(10,2) DEFAULT NULL,
  `new_rate` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `agent_rate_changes`
--

INSERT INTO `agent_rate_changes` (`id`, `agent_id`, `affiliate_id`, `old_rate`, `new_rate`, `created_at`) VALUES
(1, 106, 39, 15.00, 15.00, '2025-08-02 04:29:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT 0,
  `file_type` varchar(100) DEFAULT NULL,
  `width` int(11) DEFAULT 0,
  `height` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `position` varchar(50) DEFAULT 'header',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `bets`
--

CREATE TABLE `bets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `symbols` varchar(255) DEFAULT NULL,
  `win` tinyint(1) DEFAULT NULL,
  `prize` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `bets`
--

INSERT INTO `bets` (`id`, `user_id`, `symbols`, `win`, `prize`, `created_at`) VALUES
(1, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-07 19:04:34'),
(2, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-07 19:04:41'),
(3, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-07 19:04:45'),
(4, 1, 'coroa.png,estrela.png,moeda.png', 0, 0.00, '2025-07-07 19:04:46'),
(5, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-07 19:04:56'),
(6, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-07 19:05:48'),
(7, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-07 19:06:01'),
(8, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-07 19:06:16'),
(9, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-07 19:06:27'),
(10, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 01:46:07'),
(11, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 01:46:13'),
(12, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 01:46:18'),
(13, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 01:46:21'),
(14, 1, 'moeda.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 01:46:23'),
(15, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 01:46:23'),
(16, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 01:46:24'),
(17, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 01:46:25'),
(18, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 01:46:25'),
(19, 1, 'coroa.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 01:46:26'),
(20, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 01:46:27'),
(21, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 01:46:27'),
(22, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 01:46:28'),
(23, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 01:46:29'),
(24, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 01:46:29'),
(25, 1, 'estrela.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 01:46:30'),
(26, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 01:46:31'),
(27, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:11:27'),
(28, 1, 'estrela.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:12:50'),
(29, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:12:55'),
(30, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:12:57'),
(31, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:12:59'),
(32, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:01'),
(33, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:13:02'),
(34, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:03'),
(35, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:04'),
(36, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:04'),
(37, 1, 'coroa.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:05'),
(38, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:05'),
(39, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:06'),
(40, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:06'),
(41, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:06'),
(42, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:06'),
(43, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:13:07'),
(44, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:07'),
(45, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 02:13:07'),
(46, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:07'),
(47, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:07'),
(48, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:08'),
(49, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:08'),
(50, 1, 'moeda.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:13:08'),
(51, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:08'),
(52, 1, 'moeda.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:13:08'),
(53, 1, 'coroa.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:09'),
(54, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:09'),
(55, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:09'),
(56, 1, 'coroa.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:09'),
(57, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:09'),
(58, 1, 'moeda.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:13:10'),
(59, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:10'),
(60, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:10'),
(61, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:10'),
(62, 1, 'estrela.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:11'),
(63, 1, 'coroa.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:11'),
(64, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:11'),
(65, 1, 'estrela.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:13:11'),
(66, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:11'),
(67, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:13:12'),
(68, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:13:12'),
(69, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:12'),
(70, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:13:12'),
(71, 1, 'estrela.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:13:12'),
(72, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:13'),
(73, 1, 'coroa.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:13'),
(74, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:13:13'),
(75, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:13'),
(76, 1, 'coroa.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:13:13'),
(77, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:14'),
(78, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:14'),
(79, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:13:14'),
(80, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:14'),
(81, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 02:13:15'),
(82, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:15'),
(83, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 02:13:15'),
(84, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:15'),
(85, 1, 'coroa.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:13:15'),
(86, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:16'),
(87, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:16'),
(88, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:13:17'),
(89, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:13:18'),
(90, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:20'),
(91, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:21'),
(92, 1, 'coroa.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:22'),
(93, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:13:22'),
(94, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 02:13:22'),
(95, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:22'),
(96, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:22'),
(97, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:13:23'),
(98, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:23'),
(99, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:23'),
(100, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:23'),
(101, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:13:23'),
(102, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:24'),
(103, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:24'),
(104, 1, 'moeda.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:13:25'),
(105, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:25'),
(106, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:13:26'),
(107, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:26'),
(108, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 02:13:27'),
(109, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:13:27'),
(110, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:13:28'),
(111, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 02:13:28'),
(112, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:13:29'),
(113, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:13:30'),
(114, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:22:25'),
(115, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:22:27'),
(116, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:22:28'),
(117, 1, 'moeda.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:22:29'),
(118, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:22:29'),
(119, 1, 'coroa.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:23:59'),
(120, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 02:24:01'),
(121, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:24:02'),
(122, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:24:04'),
(123, 1, 'coroa.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:24:10'),
(124, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:24:16'),
(125, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:24:19'),
(126, 1, 'estrela.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 02:24:20'),
(127, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 02:24:27'),
(128, 1, 'coroa.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 02:24:29'),
(129, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 02:24:30'),
(130, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 02:24:32'),
(131, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 02:24:35'),
(132, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 02:50:20'),
(133, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 02:51:15'),
(134, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:31:05'),
(135, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 03:31:15'),
(136, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 03:31:17'),
(137, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-08 03:31:21'),
(138, 1, 'moeda.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 03:31:24'),
(139, 1, 'estrela.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 03:31:27'),
(140, 1, 'estrela.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 03:31:29'),
(141, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 03:31:31'),
(142, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 03:31:33'),
(143, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 03:31:36'),
(144, 1, 'coroa.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:31:42'),
(145, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:31:46'),
(146, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:34:17'),
(147, 1, 'estrela.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:34:20'),
(148, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 03:34:21'),
(149, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:34:22'),
(150, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 03:34:23'),
(151, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 03:34:23'),
(152, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-08 03:36:00'),
(153, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:03'),
(154, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:36:04'),
(155, 1, 'estrela.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 03:36:05'),
(156, 1, 'coroa.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 03:36:06'),
(157, 1, 'estrela.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 03:36:07'),
(158, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:08'),
(159, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:36:08'),
(160, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:09'),
(161, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:36:09'),
(162, 1, 'coroa.png,moeda.png,moeda.png', 0, 0.00, '2025-07-08 03:36:10'),
(163, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:10'),
(164, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:11'),
(165, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-08 03:36:11'),
(166, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 03:36:11'),
(167, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:36:12'),
(168, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:36:12'),
(169, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:36:12'),
(170, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 03:36:12'),
(171, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:36:13'),
(172, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 03:36:13'),
(173, 1, 'estrela.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:36:13'),
(174, 1, 'moeda.png,coroa.png,moeda.png', 0, 0.00, '2025-07-08 03:36:13'),
(175, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 03:36:13'),
(176, 1, 'coroa.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 03:36:13'),
(177, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:36:14'),
(178, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:14'),
(179, 1, 'moeda.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 03:36:14'),
(180, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:36:14'),
(181, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:36:14'),
(182, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:36:15'),
(183, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:36:15'),
(184, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:36:15'),
(185, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:36:15'),
(186, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:36:15'),
(187, 1, 'estrela.png,estrela.png,moeda.png', 0, 0.00, '2025-07-08 03:36:16'),
(188, 1, 'estrela.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:36:16'),
(189, 1, 'moeda.png,coroa.png,coroa.png', 0, 0.00, '2025-07-08 03:36:16'),
(190, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:36:17'),
(191, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:36:17'),
(192, 1, 'coroa.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:36:18'),
(193, 1, 'coroa.png,coroa.png,coroa.png', 1, 5.00, '2025-07-08 03:36:18'),
(194, 1, 'moeda.png,moeda.png,coroa.png', 0, 0.00, '2025-07-08 03:36:18'),
(195, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:18'),
(196, 1, 'moeda.png,moeda.png,moeda.png', 1, 5.00, '2025-07-08 03:36:18'),
(197, 1, 'estrela.png,estrela.png,estrela.png', 1, 5.00, '2025-07-08 03:36:19'),
(198, 1, 'moeda.png,moeda.png,estrela.png', 0, 0.00, '2025-07-08 03:36:19'),
(199, 1, 'moeda.png,coroa.png,estrela.png', 0, 0.00, '2025-07-08 03:36:19'),
(200, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-08 03:36:21'),
(386, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:42'),
(387, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:42'),
(388, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(389, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(390, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(391, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(392, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(393, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(394, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(395, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:43'),
(396, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:44'),
(397, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:44'),
(398, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:44'),
(399, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:44'),
(400, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:45'),
(401, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:06:51'),
(402, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:20'),
(403, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:20'),
(404, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:20'),
(405, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:20'),
(406, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:20'),
(407, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:21'),
(408, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:21'),
(409, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:21'),
(410, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:21'),
(411, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:07:21'),
(412, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:10'),
(413, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:11'),
(414, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:11'),
(415, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:11'),
(416, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:11'),
(417, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(418, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(419, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(420, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(421, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(422, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(423, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:12'),
(424, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:13'),
(425, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:13'),
(426, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:13'),
(427, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:13'),
(428, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:13'),
(429, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:14'),
(430, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:15'),
(431, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:15'),
(432, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:15'),
(433, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:15'),
(434, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:15'),
(435, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:15'),
(436, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(437, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(438, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(439, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(440, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(441, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(442, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(443, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(444, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(445, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(446, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:16'),
(447, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(448, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(449, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(450, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(451, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(452, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(453, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(454, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(455, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(456, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:17'),
(457, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:18'),
(458, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:20:19'),
(459, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:22:39'),
(460, 1, 'estrela.png,estrela.png,coroa.png', 0, 0.00, '2025-07-10 03:22:40'),
(461, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:19'),
(462, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:19'),
(463, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:19'),
(464, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:20'),
(465, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:20'),
(466, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:21'),
(467, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:21'),
(468, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:21'),
(469, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:22'),
(470, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:22'),
(471, 1, 'moeda.png,estrela.png,estrela.png', 0, 0.00, '2025-07-10 14:41:22'),
(472, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:26'),
(473, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:26'),
(474, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:27'),
(475, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:27'),
(476, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:27'),
(477, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:27'),
(478, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:27'),
(479, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:27'),
(480, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:28'),
(481, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:28'),
(482, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:29'),
(483, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:29'),
(484, 1, 'estrela.png,moeda.png,moeda.png', 0, 0.00, '2025-07-10 20:27:29'),
(485, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:35'),
(486, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:35'),
(487, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:36'),
(488, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:36'),
(489, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:36'),
(490, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(491, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(492, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(493, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(494, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(495, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(496, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:37'),
(497, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(498, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(499, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(500, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(501, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(502, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(503, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(504, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:38'),
(505, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:59'),
(506, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:59'),
(507, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:48:59'),
(508, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:24'),
(509, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:25'),
(510, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:26'),
(511, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:26'),
(512, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:26'),
(513, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:26'),
(514, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:26'),
(515, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:27'),
(516, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:27'),
(517, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:27'),
(518, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:27'),
(519, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:27'),
(520, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:28'),
(521, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:28'),
(522, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:29'),
(523, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:49:29'),
(524, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:50:12'),
(525, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:50:12'),
(526, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:50:12'),
(527, 1, 'coroa.png,moeda.png,estrela.png', 0, 0.00, '2025-07-10 20:50:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `bonus_history`
--

CREATE TABLE `bonus_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL,
  `rollover_required` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `status` enum('active','completed','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `carousel_banners`
--

CREATE TABLE `carousel_banners` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `carousel_banners`
--

INSERT INTO `carousel_banners` (`id`, `image_url`, `position`) VALUES
(1, '/img/carousel_6894988605c92.webp', 1),
(2, '/img/carousel_689498810b69c.webp', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `commissions`
--

CREATE TABLE `commissions` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `referred_user_id` int(11) NOT NULL,
  `type` enum('CPA','RevShare') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `level` int(11) NOT NULL,
  `status` enum('pending','approved','paid','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `commissions`
--

INSERT INTO `commissions` (`id`, `affiliate_id`, `referred_user_id`, `type`, `amount`, `level`, `status`, `created_at`) VALUES
(5, 38, 107, 'CPA', 0.00, 1, 'pending', '2025-08-02 04:29:36'),
(114, 41, 181, 'CPA', 0.00, 1, 'pending', '2025-08-07 12:58:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `name` varchar(50) NOT NULL,
  `value` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('rtp', '0');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(50) DEFAULT NULL,
  `valor` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`) VALUES
(1, 'rtp', '1'),
(2, 'bspay_client_id', '87j6h54rf3ed'),
(3, 'bspay_client_secret', 'k87jy6h5tg4rf3dew'),
(4, 'bspay_webhook_url', 'https://seu-site.fun/webhook_bspay_novo.php'),
(20, 'chance_vitoria', '1'),
(23, 'bspay_api_provider', 'pixup'),
(24, 'logo_principal', '/img/logo.webp'),
(25, 'logo_rodape', '/img/logo.webp'),
(26, 'suporte_tipo', 'telegram'),
(27, 'suporte_telegram_usuario', 'SeuContatoDeSuporte'),
(28, 'suporte_whatsapp_numero', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `custom_prizes`
--

CREATE TABLE `custom_prizes` (
  `id` int(11) NOT NULL,
  `prize_name` varchar(100) NOT NULL COMMENT 'Nome do prêmio (ex: 3 repetições)',
  `prize_value` decimal(10,2) NOT NULL COMMENT 'Valor do prêmio em reais',
  `occurrence_count` int(11) NOT NULL DEFAULT 1 COMMENT 'Quantas vezes deve sair',
  `current_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Quantas vezes já saiu',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se o prêmio está ativo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `custom_prizes`
--

INSERT INTO `custom_prizes` (`id`, `prize_name`, `prize_value`, `occurrence_count`, `current_count`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '3 repetições de R$2', 6.00, 2, 0, 1, '2025-07-19 11:56:18', '2025-07-19 11:56:18'),
(2, '3 repetições de R$5', 15.00, 1, 0, 1, '2025-07-19 11:56:18', '2025-07-19 11:56:18'),
(3, '3 repetições de R$10', 30.00, 1, 0, 1, '2025-07-19 11:56:18', '2025-07-19 11:56:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `depositos`
--

CREATE TABLE `depositos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `metodo` varchar(50) DEFAULT 'pix',
  `codigo_transacao` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_aprovacao` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `external_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `amount`, `status`, `payment_id`, `created_at`, `updated_at`, `external_id`) VALUES
(25, 108, 10.00, 'pago', '4c784e6fcce0f7153203mdtr8ty418z9', '2025-08-02 04:31:01', '2025-08-02 04:31:21', 'DEP_108_1754109061_7760');

-- --------------------------------------------------------

--
-- Estrutura para tabela `facebook_pixels`
--

CREATE TABLE `facebook_pixels` (
  `id` int(11) NOT NULL,
  `pixel_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_config`
--

CREATE TABLE `game_config` (
  `id` int(11) NOT NULL,
  `payment_percentage` decimal(5,2) NOT NULL DEFAULT 50.00 COMMENT 'Porcentagem de pagamento (0-100%)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se o sistema está ativo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `game_config`
--

INSERT INTO `game_config` (`id`, `payment_percentage`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 50.00, 1, '2025-07-19 11:56:18', '2025-07-19 11:56:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `game_history`
--

CREATE TABLE `game_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bet_amount` decimal(10,2) NOT NULL,
  `prize_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `symbols` text NOT NULL COMMENT 'Símbolos sorteados (JSON)',
  `won` tinyint(1) NOT NULL DEFAULT 0,
  `game_type` varchar(50) NOT NULL DEFAULT 'normal' COMMENT 'normal, influencer, custom_prize',
  `config_used` text DEFAULT NULL COMMENT 'Configuração usada no momento do jogo (JSON)',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `global_settings`
--

CREATE TABLE `global_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `global_settings`
--

INSERT INTO `global_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'default_revshare_rate', '5.0', 'Taxa padrão de RevShare (%)', '2025-07-13 20:28:17'),
(2, 'min_payout_amount', '10.00', 'Valor mínimo para saque (R$)', '2025-07-13 20:28:17'),
(3, 'max_payout_amount', '5000.00', 'Valor máximo para saque (R$)', '2025-07-13 20:28:17'),
(4, 'affiliate_system_enabled', '1', 'Sistema de afiliados ativo (1=Sim, 0=Não)', '2025-07-13 20:28:17'),
(5, 'auto_approve_payouts', '0', 'Aprovar saques automaticamente (1=Sim, 0=Não)', '2025-07-13 20:28:17'),
(6, 'commission_delay_hours', '0', 'Delay para liberar comissões (horas)', '2025-07-21 06:39:10'),
(7, 'max_affiliate_levels', '4', 'Número máximo de níveis de afiliados', '2025-07-13 20:28:17'),
(8, 'level_2_percentage', '20', 'Porcentagem do nível 2 (% da comissão do nível 1)', '2025-07-13 20:28:17'),
(9, 'level_3_percentage', '10', 'Porcentagem do nível 3 (% da comissão do nível 1)', '2025-07-13 20:28:17'),
(10, 'level_4_percentage', '5', 'Porcentagem do nível 4 (% da comissão do nível 1)', '2025-07-13 20:28:17'),
(154, 'min_deposit_amount', '5', 'Valor mínimo para depósito global (R$)', '2025-08-12 05:34:27'),
(155, 'initial_bonus_amount', '1', 'Valor do bônus inicial para novos usuários (R$)', '2025-08-03 20:13:53'),
(156, 'initial_bonus_enabled', '1', 'Bônus inicial ativo (1=Sim, 0=Não)', '2025-07-14 05:15:49'),
(2270, 'pushover_api_token', '9lk8j7h65gf4d3s2', 'API Token para notificações Pushover', '2025-08-07 15:29:16'),
(2271, 'pushover_user_key', '9k8j7h6g5f4red', 'User Key para notificações Pushover', '2025-08-07 15:29:16'),
(2272, 'pushover_enabled', '1', 'Ativar notificações Pushover (1=Sim, 0=Não)', '2025-08-07 15:29:16'),
(2273, 'pushover_notify_pix_generated', '1', 'Notificar quando PIX for gerado (1=Sim, 0=Não)', '2025-08-07 15:41:58'),
(2274, 'pushover_notify_pix_paid', '1', 'Notificar quando PIX for pago (1=Sim, 0=Não)', '2025-08-07 15:27:02'),
(2506, 'require_password_confirmation', '0', 'Exigir confirmação de senha no registro (1=Sim, 0=Não)', '2025-08-08 20:34:10'),
(5909, 'site_name', 'Raspa Green', 'Nome do site exibido no cabeçalho e outras áreas', '2025-08-12 00:04:24'),
(6155, 'deposit_bonus_enabled', '1', 'Bônus de depósito ativo (1=Sim, 0=Não)', '2025-08-12 05:34:24'),
(6156, 'deposit_bonus_percentage', '100', 'Porcentagem do bônus de depósito (100 = dobrar valor)', '2025-08-12 05:34:24'),
(6157, 'rollover_multiplier', '3', 'Multiplicador do rollover (ex: 3x o valor do bônus)', '2025-08-12 05:34:24'),
(6158, 'min_deposit_for_bonus', '5', 'Valor mínimo de depósito para receber bônus (R$)', '2025-08-12 05:35:13'),
(6291, 'double_deposit_enabled', '1', 'Depósito em dobro ativo (1=Sim, 0=Não)', '2025-08-13 02:47:13'),
(6292, 'double_deposit_rollover_multiplier', '3', 'Multiplicador do rollover para liberar bônus (ex: 3x o valor do bônus)', '2025-08-13 02:15:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `influencer_mode`
--

CREATE TABLE `influencer_mode` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID do usuário influenciador',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se o modo está ativo para este usuário',
  `win_percentage` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT 'Porcentagem de vitória (0-100%)',
  `prize_value` decimal(10,2) NOT NULL DEFAULT 10.00 COMMENT 'Valor do prêmio quando ganhar',
  `max_wins` int(11) DEFAULT NULL COMMENT 'Máximo de vitórias (NULL = ilimitado)',
  `current_wins` int(11) NOT NULL DEFAULT 0 COMMENT 'Vitórias atuais',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogadas`
--

CREATE TABLE `jogadas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `simbolos` varchar(255) DEFAULT NULL,
  `ganhou` tinyint(1) DEFAULT NULL,
  `premio` decimal(10,2) DEFAULT NULL,
  `aposta` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogadas_raspadinha`
--

CREATE TABLE `jogadas_raspadinha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `resultado` enum('ganhou','perdeu') NOT NULL,
  `premio` decimal(10,2) DEFAULT 0.00,
  `data_jogada` timestamp NULL DEFAULT current_timestamp(),
  `valor_aposta` decimal(10,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `jogadas_raspadinha`
--

INSERT INTO `jogadas_raspadinha` (`id`, `usuario_id`, `resultado`, `premio`, `data_jogada`, `valor_aposta`) VALUES
(1, 1, 'ganhou', 0.00, '2025-07-19 09:19:25', 1.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `affiliate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `request_date` timestamp NULL DEFAULT current_timestamp(),
  `payment_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `raspadinhas`
--

CREATE TABLE `raspadinhas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `resultado` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `raspadinha_config`
--

CREATE TABLE `raspadinha_config` (
  `id` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sistema de prêmio forçado ativo',
  `max_premios` int(11) NOT NULL DEFAULT 10 COMMENT 'Máximo de prêmios a pagar',
  `premios_pagos` int(11) NOT NULL DEFAULT 0 COMMENT 'Prêmios já pagos',
  `valor_premio` decimal(10,2) NOT NULL DEFAULT 10.00 COMMENT 'Valor do prêmio forçado',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `raspadinha_config`
--

INSERT INTO `raspadinha_config` (`id`, `ativo`, `max_premios`, `premios_pagos`, `valor_premio`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 6, 10.00, '2025-07-19 11:56:18', '2025-07-21 02:04:29');

-- --------------------------------------------------------

--
-- Estrutura para tabela `raspadinha_jogadas`
--

CREATE TABLE `raspadinha_jogadas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `saldo_antes` decimal(10,2) NOT NULL,
  `saldo_depois` decimal(10,2) NOT NULL,
  `aposta` decimal(10,2) NOT NULL,
  `ganhou` tinyint(1) NOT NULL,
  `valor_premio` decimal(10,2) NOT NULL,
  `simbolos` varchar(255) NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referred_id`, `level`, `created_at`) VALUES
(8, 106, 107, 1, '2025-08-02 04:29:36'),
(65, 116, 181, 1, '2025-08-07 12:58:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saques_pix`
--

CREATE TABLE `saques_pix` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo_chave` varchar(20) NOT NULL,
  `chave_pix` varchar(255) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `status` enum('pendente','processando','concluido','cancelado') DEFAULT 'pendente',
  `data_solicitacao` timestamp NULL DEFAULT current_timestamp(),
  `data_processamento` timestamp NULL DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `saques_pix`
--

INSERT INTO `saques_pix` (`id`, `user_id`, `valor`, `tipo_chave`, `chave_pix`, `nome_completo`, `cpf`, `status`, `data_solicitacao`, `data_processamento`, `observacoes`) VALUES
(8, 1, 10.00, 'cpf', '1231231231', 'asdas das', '123.123.123-12', 'concluido', '2025-08-02 05:16:45', '2025-08-05 07:42:34', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `transacoes`
--

CREATE TABLE `transacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `transacoes`
--

INSERT INTO `transacoes` (`id`, `usuario_id`, `tipo`, `valor`, `descricao`, `data_criacao`, `status`) VALUES
(3429, 1, 'premio_raspadinha', 25.00, 'Prêmio ganho na raspadinha', '2025-08-09 06:33:36', 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 10.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `referrer_id` int(11) DEFAULT NULL,
  `affiliate_status` tinyint(1) DEFAULT 0,
  `affiliate_balance` decimal(10,2) DEFAULT 0.00,
  `is_agent` tinyint(1) DEFAULT 0,
  `influence_mode_enabled` tinyint(1) DEFAULT 0,
  `telefone` varchar(15) NOT NULL DEFAULT '',
  `bonus_balance` decimal(10,2) DEFAULT 0.00,
  `bonus_rollover_required` decimal(10,2) DEFAULT 0.00,
  `bonus_rollover_completed` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `two_factor_secret`, `balance`, `created_at`, `is_admin`, `referrer_id`, `affiliate_status`, `affiliate_balance`, `is_agent`, `influence_mode_enabled`, `telefone`, `bonus_balance`, `bonus_rollover_required`, `bonus_rollover_completed`) VALUES
(1, 'admin', 'admin@admin.com', '$2a$12$tRijUVasmY3HxGN9UF4KoeYCxxKXCmfN.xzTD3lgw1QleA/4Rmy/C', 'Z2TBR6MG7PKE7POM', 10.00, '2025-07-07 18:40:25', 1, NULL, 1, 1.50, 1, 1, '', 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_referral_chain`
--

CREATE TABLE `user_referral_chain` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `influencer_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `influencer_rate` decimal(10,2) DEFAULT NULL,
  `agent_rate` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_referral_chain`
--

INSERT INTO `user_referral_chain` (`id`, `user_id`, `influencer_id`, `agent_id`, `influencer_rate`, `agent_rate`, `created_at`) VALUES
(1, 108, 107, 106, NULL, 15.00, '2025-08-02 04:30:56'),
(2, 144, 143, 98, NULL, 15.00, '2025-08-05 01:39:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `min_deposit_amount` decimal(10,2) DEFAULT NULL,
  `min_withdrawal_amount` decimal(10,2) DEFAULT NULL,
  `influence_mode_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `min_deposit_amount`, `min_withdrawal_amount`, `influence_mode_enabled`, `created_at`, `updated_at`) VALUES
(23, 107, NULL, NULL, 0, '2025-08-02 04:30:27', '2025-08-02 04:30:33'),
(37, 173, NULL, NULL, 1, '2025-08-07 02:35:01', '2025-08-07 02:35:01'),
(38, 185, NULL, NULL, 0, '2025-08-08 20:34:33', '2025-08-09 03:21:02'),
(42, 186, NULL, NULL, 0, '2025-08-09 03:04:06', '2025-08-09 03:21:09'),
(44, 2, NULL, NULL, 0, '2025-08-09 03:04:30', '2025-08-09 03:21:08'),
(51, 187, NULL, NULL, 0, '2025-08-09 03:15:44', '2025-08-09 03:20:34'),
(53, 184, NULL, NULL, 0, '2025-08-09 03:19:10', '2025-08-09 03:21:07'),
(55, 1, NULL, NULL, 1, '2025-08-09 03:19:17', '2025-08-09 06:14:59'),
(57, 188, NULL, NULL, 0, '2025-08-09 03:19:25', '2025-08-09 03:19:53'),
(68, 189, NULL, NULL, 1, '2025-08-09 03:21:42', '2025-08-09 03:21:42');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin_config`
--
ALTER TABLE `admin_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Índices de tabela `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `admin_simulated_reports`
--
ALTER TABLE `admin_simulated_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_date` (`report_date`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Índices de tabela `affiliates`
--
ALTER TABLE `affiliates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `affiliate_code` (`affiliate_code`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Índices de tabela `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Índices de tabela `affiliate_conversions`
--
ALTER TABLE `affiliate_conversions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `converted_user_id` (`converted_user_id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Índices de tabela `agent_rate_changes`
--
ALTER TABLE `agent_rate_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Índices de tabela `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `bets`
--
ALTER TABLE `bets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `bonus_history`
--
ALTER TABLE `bonus_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`),
  ADD KEY `referred_user_id` (`referred_user_id`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`name`);

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `custom_prizes`
--
ALTER TABLE `custom_prizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_custom_prizes_active` (`is_active`);

--
-- Índices de tabela `depositos`
--
ALTER TABLE `depositos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_criacao` (`data_criacao`),
  ADD KEY `idx_depositos_status_data` (`status`,`data_criacao`);

--
-- Índices de tabela `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `facebook_pixels`
--
ALTER TABLE `facebook_pixels`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `game_config`
--
ALTER TABLE `game_config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `game_history`
--
ALTER TABLE `game_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_game_history_user_date` (`user_id`,`created_at`);

--
-- Índices de tabela `global_settings`
--
ALTER TABLE `global_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Índices de tabela `influencer_mode`
--
ALTER TABLE `influencer_mode`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_influencer_mode_active` (`is_active`);

--
-- Índices de tabela `jogadas`
--
ALTER TABLE `jogadas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `jogadas_raspadinha`
--
ALTER TABLE `jogadas_raspadinha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_resultado` (`resultado`),
  ADD KEY `idx_data_jogada` (`data_jogada`),
  ADD KEY `idx_jogadas_resultado_data` (`resultado`,`data_jogada`);

--
-- Índices de tabela `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliate_id` (`affiliate_id`);

--
-- Índices de tabela `raspadinhas`
--
ALTER TABLE `raspadinhas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `raspadinha_config`
--
ALTER TABLE `raspadinha_config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `raspadinha_jogadas`
--
ALTER TABLE `raspadinha_jogadas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referred_id` (`referred_id`),
  ADD KEY `referrer_id` (`referrer_id`);

--
-- Índices de tabela `saques_pix`
--
ALTER TABLE `saques_pix`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `transacoes`
--
ALTER TABLE `transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_data_criacao` (`data_criacao`),
  ADD KEY `idx_transacoes_tipo_status` (`tipo`,`status`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `referrer_id` (`referrer_id`);

--
-- Índices de tabela `user_referral_chain`
--
ALTER TABLE `user_referral_chain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_influencer_id` (`influencer_id`),
  ADD KEY `idx_agent_id` (`agent_id`);

--
-- Índices de tabela `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_settings` (`user_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin_config`
--
ALTER TABLE `admin_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `admin_simulated_reports`
--
ALTER TABLE `admin_simulated_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `affiliates`
--
ALTER TABLE `affiliates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `affiliate_clicks`
--
ALTER TABLE `affiliate_clicks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT de tabela `affiliate_conversions`
--
ALTER TABLE `affiliate_conversions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `agent_rate_changes`
--
ALTER TABLE `agent_rate_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `bets`
--
ALTER TABLE `bets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=528;

--
-- AUTO_INCREMENT de tabela `bonus_history`
--
ALTER TABLE `bonus_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `custom_prizes`
--
ALTER TABLE `custom_prizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `depositos`
--
ALTER TABLE `depositos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT de tabela `facebook_pixels`
--
ALTER TABLE `facebook_pixels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `game_config`
--
ALTER TABLE `game_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `game_history`
--
ALTER TABLE `game_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `global_settings`
--
ALTER TABLE `global_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6713;

--
-- AUTO_INCREMENT de tabela `influencer_mode`
--
ALTER TABLE `influencer_mode`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jogadas`
--
ALTER TABLE `jogadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jogadas_raspadinha`
--
ALTER TABLE `jogadas_raspadinha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3208;

--
-- AUTO_INCREMENT de tabela `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `raspadinhas`
--
ALTER TABLE `raspadinhas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `raspadinha_config`
--
ALTER TABLE `raspadinha_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `raspadinha_jogadas`
--
ALTER TABLE `raspadinha_jogadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `saques_pix`
--
ALTER TABLE `saques_pix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `transacoes`
--
ALTER TABLE `transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3767;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT de tabela `user_referral_chain`
--
ALTER TABLE `user_referral_chain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `admin_simulated_reports`
--
ALTER TABLE `admin_simulated_reports`
  ADD CONSTRAINT `admin_simulated_reports_ibfk_1` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`);

--
-- Restrições para tabelas `bonus_history`
--
ALTER TABLE `bonus_history`
  ADD CONSTRAINT `bonus_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



-- Add option to show/hide rollover box in profile
INSERT INTO global_settings (setting_key, setting_value, description) 
VALUES ('show_rollover_box', '0', 'Exibir caixa de rollover no perfil do usuário (1=Sim, 0=Não)');

-- Add option to enable/disable double deposit globally (in case it doesn't exist yet)
INSERT IGNORE INTO global_settings (setting_key, setting_value, description) 
VALUES ('double_deposit_enabled', '0', 'Depósito em dobro ativo (1=Sim, 0=Não)');

-- Add a column to the deposits table to track user preference for double deposit (if needed)
ALTER TABLE deposits ADD COLUMN double_deposit_opted TINYINT(1) DEFAULT 0;

CREATE TABLE `kwai_pixels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pixel_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO global_settings (setting_key, setting_value, description) 
VALUES ('deduct_withdrawal_from_affiliate', '1', 'Descontar saques dos usuários indicados do saldo do afiliado (1=Sim, 0=Não)');
-- Criar tabela para pixels do TikTok
CREATE TABLE `tiktok_pixels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pixel_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pixel_id` (`pixel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;