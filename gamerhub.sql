-- phpMyAdmin SQL Dump
-- version 5.2.1

-- Host: 127.0.0.1
-- Tempo de geração: 23-Jul-2026
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

CREATE DATABASE IF NOT EXISTS `gamerhub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `gamerhub`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gamerhub`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categories`
--

INSERT INTO `categories` (`id`, `nome`, `descricao`, `criado_em`) VALUES
(1, 'Teclados', 'Teclados gaming mecânicos e de membrana.', '2026-07-15 07:48:22'),
(2, 'Ratos', 'Ratos gaming de elevada precisão.', '2026-07-15 07:48:22'),
(3, 'Headsets', 'Headsets e auscultadores para gaming.', '2026-07-15 07:48:22'),
(4, 'Monitores', 'Monitores gaming de alta frequência.', '2026-07-15 07:48:22'),
(5, 'Cadeiras Gaming', 'Cadeiras ergonómicas para setups gaming.', '2026-07-15 07:48:22'),
(6, 'Consolas', 'Consolas e sistemas de entretenimento.', '2026-07-15 07:48:22'),
(7, 'Comandos', 'Comandos e acessórios para consolas e PC.', '2026-07-15 07:48:22'),
(8, 'Componentes', 'Componentes para construção e atualização de computadores.', '2026-07-15 07:48:22'),
(9, 'Streaming', 'Microfones, câmaras e acessórios para streaming.', '2026-07-15 07:48:22'),
(10, 'Iluminação RGB', 'Iluminação decorativa para setups gaming.', '2026-07-15 07:48:22'),
(13, 'Jogos', 'Jogos para PlayStation, Xbox, Nintendo Switch e PC.', '2026-07-18 17:00:46');

-- --------------------------------------------------------

--
-- Estrutura da tabela `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data_encomenda` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendente','pago','em_preparacao','enviado','entregue','cancelado') NOT NULL DEFAULT 'pendente',
  `metodo_pagamento` enum('cartao','mbway','multibanco','paypal') NOT NULL DEFAULT 'cartao',
  `total` decimal(10,2) NOT NULL,
  `nome_envio` varchar(100) NOT NULL,
  `email_envio` varchar(150) NOT NULL,
  `telefone_envio` varchar(20) NOT NULL,
  `morada_envio` varchar(255) NOT NULL,
  `cidade_envio` varchar(100) NOT NULL,
  `codigo_postal_envio` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `data_encomenda`, `estado`, `metodo_pagamento`, `total`, `nome_envio`, `email_envio`, `telefone_envio`, `morada_envio`, `cidade_envio`, `codigo_postal_envio`) VALUES
(1, 2, '2026-07-21 17:29:23', 'enviado', 'cartao', 1649.97, 'Carolina', 'teste12@gmail.com', '+351924718617', 'azinhaga do pilar n5 blc b 2V, edificio solar do pilar', 'Funchal', '9000-690'),
(2, 2, '2026-07-22 12:20:52', 'enviado', 'cartao', 939.97, 'Carolina', 'teste12@gmail.com', '+351923748762', 'madeira', 'Funchal', '9000-000'),
(3, 3, '2026-07-22 20:13:42', 'pendente', 'cartao', 119.98, 'PetraAdmin', 'teste0303@gmail.com', '123456789', 'madeira', 'madeira', '0000000'),
(4, 4, '2026-07-22 20:30:18', 'pendente', 'mbway', 5619.92, 'Kiko', 'kiko1234@gmail.com', '924589623', 'funchal', 'funchal', '9000-000');

-- --------------------------------------------------------

--
-- Estrutura da tabela `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
(1, 1, 122, 3, 549.99, 1649.97),
(2, 2, 112, 2, 69.99, 139.98),
(3, 2, 39, 1, 799.99, 799.99),
(4, 3, 136, 2, 59.99, 119.98),
(5, 4, 82, 3, 1499.99, 4499.97),
(6, 4, 73, 2, 269.99, 539.98),
(7, 4, 105, 1, 199.99, 199.99),
(8, 4, 106, 1, 159.99, 159.99),
(9, 4, 114, 1, 219.99, 219.99);

-- --------------------------------------------------------

--
-- Estrutura da tabela `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_promocional` decimal(10,2) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `categoria_id` int(11) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `data_lancamento` date DEFAULT NULL,
  `plataforma` varchar(100) DEFAULT NULL,
  `genero` varchar(100) DEFAULT NULL,
  `developer` varchar(150) DEFAULT NULL,
  `publisher` varchar(150) DEFAULT NULL,
  `pegi` varchar(20) DEFAULT NULL,
  `destaque` tinyint(1) NOT NULL DEFAULT 0,
  `proximo_lancamento` tinyint(1) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `products`
--

INSERT INTO `products` (`id`, `nome`, `descricao`, `preco`, `preco_promocional`, `imagem`, `stock`, `categoria_id`, `marca`, `data_lancamento`, `plataforma`, `genero`, `developer`, `publisher`, `pegi`, `destaque`, `proximo_lancamento`, `ativo`, `criado_em`, `atualizado_em`) VALUES
(9, 'Grand Theft Auto VI', 'Regressa a Vice City numa nova aventura de mundo aberto.', 79.99, NULL, 'assets/images/products/games/gta-vi.jpg', 50, 13, 'Rockstar Games', '2026-11-19', 'PS5, Xbox Series X|S', 'Ação e aventura', 'Rockstar Games', 'Rockstar Games', '18', 1, 1, 1, '2026-07-17 13:51:02', '2026-07-20 17:42:47'),
(10, 'Marvel\'s Wolverine', 'Uma aventura intensa protagonizada por Wolverine.', 79.99, NULL, 'assets/images/products/games/wolverine.jpg', 40, 13, 'PlayStation Studios', '2026-09-15', 'PS5', 'Ação e aventura', 'Insomniac Games', 'Sony Interactive Entertainment', '18', 1, 1, 1, '2026-07-17 13:51:02', '2026-07-20 17:42:47'),
(11, 'Silent Hill: Townfall', 'Uma nova experiência de terror psicológico.', 69.99, NULL, 'assets/images/products/games/silent-hill-townfall.jpg', 30, 13, 'Konami', '2026-09-24', 'PS5, PC', 'Terror psicológico', 'No Code', 'Konami', '18', 1, 1, 1, '2026-07-17 13:51:02', '2026-07-20 17:42:47'),
(12, '007 First Light', 'Descobre a história de origem de um jovem James Bond.', 74.99, NULL, 'assets/images/products/games/007-first-light.jpg', 35, 13, 'IO Interactive', '2026-05-27', 'PS5, Xbox Series X|S, PC', 'Ação e espionagem', 'IO Interactive', 'IO Interactive', '16', 1, 1, 1, '2026-07-17 13:51:02', '2026-07-20 17:42:47'),
(28, 'Ratchet & Clank: Rift Apart', 'Viaja entre dimensões numa aventura de ação repleta de mundos coloridos e armas criativas.', 69.99, 49.99, 'assets/images/products/games/ratchet-and-clank-rift-apart.jpg', 35, 13, 'PlayStation Studios', '2021-06-11', 'Multiplataforma', 'Ação e plataformas', 'Insomniac Games', 'Sony Interactive Entertainment', '7', 1, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:42:47'),
(29, 'Demon\'s Souls', 'Enfrenta criaturas aterradoras num desafiante RPG de ação reconstruído para uma nova geração.', 79.99, 49.99, 'assets/images/products/games/demons-souls.jpg', 22, 13, 'PlayStation Studios', '2020-11-12', 'PS5', 'RPG de ação e soulslike', 'Bluepoint Games', 'Sony Interactive Entertainment', '18', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:40:43'),
(30, 'Returnal', 'Quebra o ciclo de um planeta alienígena numa experiência intensa de ação, ficção científica e roguelike.', 79.99, 39.99, 'assets/images/products/games/returnal.jpg', 20, 13, 'PlayStation Studios', '2021-04-30', 'Multiplataforma', 'Ação e roguelike', 'Housemarque', 'Sony Interactive Entertainment', '16', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-19 12:15:53'),
(31, 'Final Fantasy XVI', 'Uma história épica de cristais, reinos em guerra e poderosos Eikons.', 79.99, 54.99, 'assets/images/products/games/final-fantasy-xvi.jpg', 28, 13, 'Square Enix', '2023-06-22', 'Multiplataforma', 'RPG de ação', 'Square Enix Creative Studio III', 'Square Enix', '18', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:40:43'),
(32, 'Final Fantasy VII Rebirth', 'Cloud e os seus companheiros exploram um vasto mundo numa nova etapa da sua aventura.', 79.99, 64.99, 'assets/images/products/games/final-fantasy-vii-rebirth.jpg', 32, 13, 'Square Enix', '2024-02-29', 'Multiplataforma', 'RPG', 'Square Enix', 'Square Enix', '16', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:40:43'),
(33, 'Stellar Blade', 'Combate criaturas poderosas numa aventura futurista de ação protagonizada por Eve.', 79.99, 69.99, 'assets/images/products/games/stellar-blade.jpg', 30, 13, 'PlayStation Studios', '2024-04-26', 'Multiplataforma', 'RPG de ação', 'Shift Up', 'Sony Interactive Entertainment', '18', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:40:43'),
(34, 'Astro Bot', 'Explora dezenas de mundos criativos numa celebração divertida do universo PlayStation.', 69.99, NULL, 'assets/images/products/games/astro-bot.jpg', 40, 13, 'PlayStation Studios', '2024-09-06', 'PS5', 'Plataformas', 'Team Asobi', 'Sony Interactive Entertainment', '7', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:40:43'),
(35, 'Rise of the Ronin', 'Cria o teu guerreiro e explora o Japão do século XIX num RPG de ação em mundo aberto.', 79.99, 59.99, 'assets/images/products/games/rise-of-the-ronin.jpg', 24, 13, 'PlayStation Studios', '2024-03-22', 'Multiplataforma', 'RPG de ação', 'Team Ninja', 'Sony Interactive Entertainment', '18', 0, 0, 1, '2026-07-18 18:11:31', '2026-07-19 12:15:53'),
(36, 'Helldivers 2', 'Junta-te aos Helldivers e combate ameaças alienígenas em intensas missões cooperativas.', 39.99, NULL, 'assets/images/products/games/helldivers-2.jpg', 45, 13, 'PlayStation Studios', '2024-02-08', 'Multiplataforma', 'Ação cooperativa', 'Arrowhead Game Studios', 'Sony Interactive Entertainment', '18', 1, 0, 1, '2026-07-18 18:11:31', '2026-07-20 17:42:47'),
(39, 'PlayStation 5 Pro', 'Consola PlayStation 5 Pro criada para oferecer maior desempenho gráfico e uma experiência premium.', 799.99, NULL, 'assets/images/products/consoles/ps5-pro.jpg', 8, 6, 'Sony', NULL, 'PlayStation 5', 'Consola', 'Sony Interactive Entertainment', 'Sony Interactive Entertainment', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-22 12:20:52'),
(40, 'Xbox Series X', 'Consola Xbox de elevado desempenho, ideal para jogos em alta resolução e carregamentos rápidos.', 599.99, 549.99, 'assets/images/products/consoles/xbox-series-x.jpg', 12, 6, 'Microsoft', NULL, 'Xbox Series X|S', 'Consola', 'Microsoft', 'Microsoft', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-20 17:40:43'),
(41, 'Xbox Series S 1TB', 'Consola Xbox totalmente digital com armazenamento de 1 TB e design compacto.', 399.99, 379.99, 'assets/images/products/consoles/xbox-series-s-1tb.jpg', 18, 6, 'Microsoft', NULL, 'Xbox Series X|S', 'Consola', 'Microsoft', 'Microsoft', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-19 12:16:08'),
(42, 'Nintendo Switch OLED', 'Consola híbrida Nintendo Switch com ecrã OLED, modo portátil e ligação à televisão.', 349.99, 329.99, 'assets/images/products/consoles/nintendo-switch-oled.jpg', 20, 6, 'Nintendo', NULL, 'Nintendo Switch', 'Consola híbrida', 'Nintendo', 'Nintendo', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-20 17:40:43'),
(43, 'Nintendo Switch 2', 'Nova geração da consola híbrida Nintendo, preparada para jogar em casa ou em qualquer lugar.', 499.99, NULL, 'assets/images/products/consoles/nintendo-switch-2.jpg', 11, 6, 'Nintendo', NULL, 'Nintendo Switch 2', 'Consola híbrida', 'Nintendo', 'Nintendo', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-20 17:40:43'),
(44, 'Steam Deck OLED', 'Consola portátil para jogos de PC com ecrã OLED e acesso à biblioteca Steam.', 679.99, 649.99, 'assets/images/products/consoles/steam-deck-oled.jpg', 10, 6, 'Valve', NULL, 'PC', 'Consola portátil', 'Valve', 'Valve', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-20 17:40:43'),
(45, 'ASUS ROG Ally X', 'Consola portátil gaming com Windows, elevada capacidade e desempenho para jogos de PC.', 899.99, 849.99, 'assets/images/products/consoles/asus-rog-ally-x.jpg', 8, 6, 'ASUS', NULL, 'PC', 'Consola portátil', 'ASUS', 'ASUS', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-19 12:16:08'),
(46, 'Lenovo Legion Go', 'Consola portátil gaming com ecrã amplo, comandos destacáveis e sistema Windows.', 799.99, 749.99, 'assets/images/products/consoles/lenovo-legion-go.jpg', 9, 6, 'Lenovo', NULL, 'PC', 'Consola portátil', 'Lenovo', 'Lenovo', NULL, 0, 0, 1, '2026-07-19 11:36:18', '2026-07-19 12:16:08'),
(47, 'SteelSeries Arctis Nova Pro Wireless', 'Headset gaming sem fios premium com cancelamento ativo de ruído, áudio espacial e estação base.', 379.99, 349.99, 'assets/images/products/headsets/steelseries-arctis-nova-pro-wireless.jpg', 12, 3, 'SteelSeries', NULL, 'PC, PS5', 'Headset gaming', 'SteelSeries', 'SteelSeries', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-20 17:40:43'),
(48, 'Logitech G Pro X 2 Lightspeed', 'Headset gaming sem fios com drivers de grafeno, microfone removível e ligação LIGHTSPEED.', 269.99, 239.99, 'assets/images/products/headsets/logitech-g-pro-x-2-lightspeed.jpg', 16, 3, 'Logitech', NULL, 'PC, PS5, Nintendo Switch', 'Headset gaming', 'Logitech G', 'Logitech', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-20 17:40:43'),
(49, 'Razer BlackShark V2 Pro', 'Headset competitivo sem fios com microfone de banda larga e elevado isolamento acústico.', 219.99, 189.99, 'assets/images/products/headsets/razer-blackshark-v2-pro.jpg', 18, 3, 'Razer', NULL, 'PC, PS5', 'Headset gaming', 'Razer', 'Razer', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-20 17:40:43'),
(50, 'Sony Pulse Elite', 'Headset sem fios oficial PlayStation com drivers magnéticos planares e áudio detalhado.', 149.99, 139.99, 'assets/images/products/headsets/sony-pulse-elite.jpg', 21, 3, 'Sony', NULL, 'PS5, PC', 'Headset gaming', 'Sony Interactive Entertainment', 'Sony Interactive Entertainment', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-20 17:40:43'),
(51, 'Corsair HS80 Max Wireless', 'Headset confortável com som Dolby Atmos, microfone omnidirecional e autonomia prolongada.', 189.99, 159.99, 'assets/images/products/headsets/corsair-hs80-max.jpg', 15, 3, 'Corsair', NULL, 'PC, PS5', 'Headset gaming', 'Corsair', 'Corsair', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-19 12:20:38'),
(52, 'Turtle Beach Stealth Pro', 'Headset premium multiplataforma com cancelamento de ruído e baterias substituíveis.', 329.99, 299.99, 'assets/images/products/headsets/turtle-beach-stealth-pro.jpg', 10, 3, 'Turtle Beach', NULL, 'PC, PS5, Xbox', 'Headset gaming', 'Turtle Beach', 'Turtle Beach', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-19 12:20:38'),
(53, 'Astro A50 X', 'Headset sem fios com estação base HDMI, alternância entre plataformas e áudio de alta qualidade.', 399.99, 369.99, 'assets/images/products/headsets/astro-a50-x.jpg', 8, 3, 'Astro Gaming', NULL, 'PC, PS5, Xbox', 'Headset gaming', 'Logitech G', 'Logitech', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-20 17:40:43'),
(54, 'JBL Quantum 910 Wireless', 'Headset gaming sem fios com rastreamento de cabeça, cancelamento de ruído e som espacial.', 279.99, 249.99, 'assets/images/products/headsets/jbl-quantum-910.jpg', 13, 3, 'JBL', NULL, 'PC, PS5, Xbox', 'Headset gaming', 'JBL', 'JBL', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-19 12:20:38'),
(55, 'ASUS ROG Delta II', 'Headset gaming sem fios com áudio de alta resolução, iluminação RGB e ligação multiplataforma.', 249.99, 219.99, 'assets/images/products/headsets/asus-rog-delta-ii.jpg', 11, 3, 'ASUS', NULL, 'PC, PS5, Nintendo Switch', 'Headset gaming', 'ASUS ROG', 'ASUS', NULL, 0, 0, 1, '2026-07-19 12:20:38', '2026-07-19 12:20:38'),
(56, 'Razer Viper V3 Pro', 'Rato gaming sem fios ultraleve, desenvolvido para competição e movimentos rápidos de elevada precisão.', 179.99, 159.99, 'assets/images/products/mice/razer-viper-v3-pro.jpg', 18, 2, 'Razer', NULL, 'PC', 'Rato gaming', 'Razer', 'Razer', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-20 17:40:43'),
(57, 'Razer DeathAdder V3 Pro', 'Rato ergonómico sem fios com sensor de alta precisão, ideal para longas sessões de jogo.', 169.99, 149.99, 'assets/images/products/mice/razer-deathadder-v3-pro.jpg', 20, 2, 'Razer', NULL, 'PC', 'Rato gaming', 'Razer', 'Razer', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-20 17:40:43'),
(58, 'Logitech G502 X Plus', 'Rato gaming sem fios com iluminação RGB, botões programáveis e design ergonómico.', 179.99, 149.99, 'assets/images/products/mice/logitech-g502-x-plus.jpg', 17, 2, 'Logitech', NULL, 'PC', 'Rato gaming', 'Logitech G', 'Logitech', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-20 17:40:43'),
(59, 'SteelSeries Aerox 5 Wireless', 'Rato sem fios leve com vários botões programáveis e estrutura otimizada para jogos competitivos.', 139.99, 119.99, 'assets/images/products/mice/steelseries-aerox-5-wireless.jpg', 15, 2, 'SteelSeries', NULL, 'PC', 'Rato gaming', 'SteelSeries', 'SteelSeries', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-19 13:03:09'),
(60, 'ASUS ROG Harpe Ace', 'Rato gaming sem fios ultraleve com sensor ótico avançado e formato desenvolvido para esports.', 149.99, 129.99, 'assets/images/products/mice/asus-rog-harpe-ace.jpg', 13, 2, 'ASUS', NULL, 'PC', 'Rato gaming', 'ASUS ROG', 'ASUS', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-19 13:03:09'),
(61, 'Corsair M75 Air Wireless', 'Rato gaming sem fios leve e minimalista, preparado para movimentos rápidos e precisos.', 149.99, 119.99, 'assets/images/products/mice/corsair-m75-air.jpg', 14, 2, 'Corsair', NULL, 'PC', 'Rato gaming', 'Corsair', 'Corsair', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-19 13:03:09'),
(62, 'Glorious Model O 2 Wireless', 'Rato gaming sem fios leve, com iluminação RGB e formato ambidestro confortável.', 119.99, 99.99, 'assets/images/products/mice/glorious-model-o-2-wireless.jpg', 19, 2, 'Glorious', NULL, 'PC', 'Rato gaming', 'Glorious', 'Glorious', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-19 13:03:09'),
(63, 'Pulsar X2V2', 'Rato gaming sem fios compacto e ultraleve, indicado para jogadores que procuram controlo e velocidade.', 109.99, 94.99, 'assets/images/products/mice/pulsar-x2v2.jpg', 16, 2, 'Pulsar', NULL, 'PC', 'Rato gaming', 'Pulsar Gaming Gears', 'Pulsar Gaming Gears', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-19 13:03:09'),
(64, 'Endgame Gear OP1 8K', 'Rato gaming com fio, baixa latência e elevada taxa de atualização para jogos competitivos.', 89.99, 79.99, 'assets/images/products/mice/endgame-gear-op1-8k.jpg', 22, 2, 'Endgame Gear', NULL, 'PC', 'Rato gaming', 'Endgame Gear', 'Endgame Gear', NULL, 0, 0, 1, '2026-07-19 13:03:09', '2026-07-19 13:03:09'),
(65, 'Logitech G915 X', 'Teclado gaming mecânico sem fios com perfil baixo, LIGHTSPEED e RGB.', 249.99, 229.99, 'assets/images/products/keyboards/logitech-g915-x.jpg', 15, 1, 'Logitech', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-20 17:40:43'),
(66, 'Razer Huntsman V3 Pro', 'Teclado óptico analógico para máxima velocidade e precisão.', 299.99, 279.99, 'assets/images/products/keyboards/razer-huntsman-v3-pro.jpg', 12, 1, 'Razer', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-20 17:40:43'),
(67, 'Corsair K70 RGB Pro', 'Teclado mecânico premium com switches Cherry MX e RGB.', 199.99, 179.99, 'assets/images/products/keyboards/corsair-k70-rgb-pro.jpg', 18, 1, 'Corsair', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-19 13:07:44'),
(68, 'ASUS ROG Azoth', 'Teclado mecânico compacto 75%, OLED e ligação sem fios.', 279.99, 259.99, 'assets/images/products/keyboards/asus-rog-azoth.jpg', 10, 1, 'ASUS', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-20 17:40:43'),
(69, 'Keychron Q1 Max', 'Teclado mecânico hot-swappable em alumínio.', 229.99, 209.99, 'assets/images/products/keyboards/keychron-q1-max.jpg', 13, 1, 'Keychron', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-19 13:07:44'),
(70, 'HyperX Alloy Origins', 'Teclado mecânico compacto em alumínio.', 139.99, 119.99, 'assets/images/products/keyboards/hyperx-alloy-origins.jpg', 16, 1, 'HyperX', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-19 13:07:44'),
(71, 'Wooting 80HE', 'Teclado Hall Effect extremamente preciso para esports.', 249.99, 229.99, 'assets/images/products/keyboards/wooting-80he.jpg', 8, 1, 'Wooting', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-20 17:40:43'),
(72, 'Ducky One 3', 'Teclado mecânico RGB com elevada qualidade de construção.', 169.99, 149.99, 'assets/images/products/keyboards/ducky-one-3.jpg', 17, 1, 'Ducky', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-19 13:07:44'),
(73, 'Mountain Everest Max', 'Teclado modular premium com numpad destacável.', 289.99, 269.99, 'assets/images/products/keyboards/mountain-everest-max.jpg', 7, 1, 'Mountain', NULL, 'PC', 'Teclado Gaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 13:07:44', '2026-07-22 20:30:18'),
(74, 'ASUS ROG Swift OLED PG32UCDM', 'Monitor gaming OLED de 32 polegadas, resolução 4K, elevada taxa de atualização e excelente qualidade de imagem.', 1499.99, 1399.99, 'assets/images/products/monitors/asus-rog-swift-pg32ucdm.jpg', 7, 4, 'ASUS', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming OLED', 'ASUS ROG', 'ASUS', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-20 17:40:43'),
(75, 'LG UltraGear OLED 32GS95UE', 'Monitor OLED de 32 polegadas com resolução 4K, resposta rápida e modo de elevada frequência para jogos competitivos.', 1399.99, 1299.99, 'assets/images/products/monitors/lg-ultragear-32gs95ue.jpg', 8, 4, 'LG', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming OLED', 'LG UltraGear', 'LG', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-20 17:40:43'),
(76, 'Samsung Odyssey OLED G8', 'Monitor gaming OLED ultrawide com imagem envolvente, elevada taxa de atualização e design premium.', 1199.99, 1099.99, 'assets/images/products/monitors/samsung-odyssey-oled-g8.jpg', 10, 4, 'Samsung', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming OLED', 'Samsung Odyssey', 'Samsung', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-20 17:40:43'),
(77, 'Alienware AW3225QF', 'Monitor gaming QD-OLED curvo de 32 polegadas com resolução 4K e elevada fluidez para jogos competitivos.', 1299.99, 1199.99, 'assets/images/products/monitors/alienware-aw3225qf.jpg', 6, 4, 'Alienware', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming QD-OLED', 'Alienware', 'Dell', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-20 17:40:43'),
(79, 'Gigabyte AORUS FO32U2', 'Monitor gaming QD-OLED de 32 polegadas com resolução 4K, resposta rápida e conectividade avançada.', 1249.99, 1149.99, 'assets/images/products/monitors/gigabyte-aorus-fo32u2.jpg', 8, 4, 'Gigabyte', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming QD-OLED', 'AORUS', 'Gigabyte', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-19 13:50:00'),
(80, 'Acer Predator X32', 'Monitor gaming de 32 polegadas com resolução 4K, HDR e desempenho indicado para jogos exigentes.', 1099.99, 999.99, 'assets/images/products/monitors/acer-predator-x32.jpg', 11, 4, 'Acer', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming 4K', 'Acer Predator', 'Acer', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-19 13:50:00'),
(81, 'BenQ MOBIUZ EX321UX', 'Monitor gaming 4K com tecnologia Mini LED, HDR e áudio integrado para uma experiência completa.', 1199.99, 1099.99, 'assets/images/products/monitors/benq-mobiuz-ex321ux.jpg', 7, 4, 'BenQ', NULL, 'PC, PS5, Xbox Series X|S', 'Monitor gaming Mini LED', 'BenQ MOBIUZ', 'BenQ', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-19 13:50:00'),
(82, 'Philips Evnia 49M2C8900', 'Monitor gaming super ultrawide QD-OLED de 49 polegadas, ideal para jogos imersivos e multitarefa.', 1599.99, 1499.99, 'assets/images/products/monitors/philips-evnia-49m2c8900.jpg', 2, 4, 'Philips', NULL, 'PC', 'Monitor gaming ultrawide', 'Philips Evnia', 'Philips', NULL, 0, 0, 1, '2026-07-19 13:50:00', '2026-07-22 20:30:18'),
(83, 'Corsair TC500 Luxe', 'Cadeira gaming ergonómica com encosto amplo, tecido respirável e apoio lombar ajustável.', 499.99, 459.99, 'assets/images/products/chairs/corsair-tc500-luxe.jpg', 10, 5, 'Corsair', NULL, 'Universal', 'Cadeira gaming', 'Corsair', 'Corsair', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-20 17:40:43'),
(84, 'Razer Iskur V2', 'Cadeira gaming com suporte lombar adaptativo, espuma moldada e acabamento premium.', 649.99, 599.99, 'assets/images/products/chairs/razer-iskur-v2.jpg', 8, 5, 'Razer', NULL, 'Universal', 'Cadeira gaming', 'Razer', 'Razer', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-20 17:40:43'),
(85, 'noblechairs HERO', 'Cadeira gaming de grandes dimensões com suporte lombar integrado e estrutura reforçada.', 449.99, 419.99, 'assets/images/products/chairs/noblechairs-hero.jpg', 11, 5, 'noblechairs', NULL, 'Universal', 'Cadeira gaming', 'noblechairs', 'noblechairs', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-19 15:50:20'),
(86, 'DXRacer Master', 'Cadeira gaming modular com apoio de cabeça magnético, suporte lombar ajustável e estrutura robusta.', 499.99, 449.99, 'assets/images/products/chairs/dxracer-master.jpg', 9, 5, 'DXRacer', NULL, 'Universal', 'Cadeira gaming', 'DXRacer', 'DXRacer', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-19 15:50:20'),
(87, 'ThunderX3 CORE', 'Cadeira gaming com tecnologia de suporte lombar dinâmico e ampla capacidade de ajuste.', 399.99, 369.99, 'assets/images/products/chairs/thunderx3-core.jpg', 13, 5, 'ThunderX3', NULL, 'Universal', 'Cadeira gaming', 'ThunderX3', 'ThunderX3', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-19 15:50:20'),
(88, 'Cougar Armor EVO', 'Cadeira gaming com estrutura em aço, apoio lombar e encosto reclinável para longas sessões.', 349.99, 319.99, 'assets/images/products/chairs/cougar-armor-evo.jpg', 14, 5, 'Cougar', NULL, 'Universal', 'Cadeira gaming', 'Cougar Gaming', 'Cougar', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-19 15:50:20'),
(89, 'AndaSeat Kaiser 4', 'Cadeira gaming espaçosa com suporte lombar ajustável, materiais premium e elevada durabilidade.', 549.99, 499.99, 'assets/images/products/chairs/andaseat-kaiser-4.jpg', 8, 5, 'AndaSeat', NULL, 'Universal', 'Cadeira gaming', 'AndaSeat', 'AndaSeat', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-20 17:40:43'),
(90, 'ASUS ROG Destrier Ergo', 'Cadeira gaming ergonómica com estrutura futurista, suporte lombar e apoio de cabeça ajustável.', 799.99, 749.99, 'assets/images/products/chairs/asus-rog-destrier-ergo.jpg', 6, 5, 'ASUS', NULL, 'Universal', 'Cadeira gaming ergonómica', 'ASUS ROG', 'ASUS', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-20 17:40:43'),
(91, 'Sharkoon SKILLER SGS40', 'Cadeira gaming confortável com encosto reclinável, apoio lombar e almofada cervical.', 299.99, 269.99, 'assets/images/products/chairs/sharkoon-skiller-sgs40.jpg', 15, 5, 'Sharkoon', NULL, 'Universal', 'Cadeira gaming', 'Sharkoon', 'Sharkoon', NULL, 0, 0, 1, '2026-07-19 14:41:59', '2026-07-19 15:50:20'),
(92, 'Nanoleaf Shapes', 'Painéis LED modulares RGB inteligentes para decoração de setups gaming.', 199.99, 179.99, 'assets/images/products/rgb/nanoleaf-shapes.jpg', 15, 10, 'Nanoleaf', NULL, 'Universal', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-20 17:40:43'),
(93, 'Nanoleaf Lines', 'Linhas LED RGB inteligentes para criar efeitos luminosos modernos.', 219.99, 199.99, 'assets/images/products/rgb/nanoleaf-lines.jpg', 10, 10, 'Nanoleaf', NULL, 'Universal', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-20 17:40:43'),
(94, 'Govee Glide', 'Barras LED RGB inteligentes compatíveis com Alexa e Google Home.', 129.99, 109.99, 'assets/images/products/rgb/govee-glide.jpg', 18, 10, 'Govee', NULL, 'Universal', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-19 15:07:10'),
(95, 'Govee Hexa', 'Painéis hexagonais RGB inteligentes para setups gaming.', 169.99, 149.99, 'assets/images/products/rgb/govee-hexa.jpg', 14, 10, 'Govee', NULL, 'Universal', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-20 17:40:43'),
(96, 'Corsair LS100', 'Kit de iluminação RGB ambiente para monitores e secretárias.', 119.99, 99.99, 'assets/images/products/rgb/corsair-ls100.jpg', 12, 10, 'Corsair', NULL, 'PC', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-19 15:07:10'),
(97, 'Corsair LT100', 'Torres RGB inteligentes para sincronização com o teu setup.', 139.99, 119.99, 'assets/images/products/rgb/corsair-lt100.jpg', 11, 10, 'Corsair', NULL, 'PC', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-19 15:07:10'),
(98, 'Philips Hue Play Bars', 'Barras LED premium para criar iluminação ambiente atrás do monitor.', 149.99, 129.99, 'assets/images/products/rgb/philips-hue-play-bars.jpg', 13, 10, 'Philips', NULL, 'Universal', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-20 17:40:43'),
(99, 'Elgato Key Light', 'Painel LED profissional para streamers e criadores de conteúdo.', 199.99, 179.99, 'assets/images/products/rgb/elgato-key-light.jpg', 9, 10, 'Elgato', NULL, 'Universal', 'Iluminação para Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-20 17:40:43'),
(100, 'Elgato Key Light Mini', 'Painel LED portátil ideal para gravações e streaming.', 119.99, 99.99, 'assets/images/products/rgb/elgato-key-light-mini.jpg', 15, 10, 'Elgato', NULL, 'Universal', 'Iluminação para Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-19 15:07:10'),
(101, 'NZXT RGB Kit', 'Kit RGB completo para personalização do interior do computador.', 89.99, 74.99, 'assets/images/products/rgb/nzxt-rgb-kit.jpg', 20, 10, 'NZXT', NULL, 'PC', 'Iluminação RGB', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:07:10', '2026-07-19 15:07:10'),
(102, 'Elgato Stream Deck XL', 'Controlador com 32 teclas LCD personalizáveis para streaming e produtividade.', 269.99, 249.99, 'assets/images/products/streaming/elgato-stream-deck-xl.jpg', 12, 9, 'Elgato', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-20 17:40:43'),
(103, 'Elgato Facecam Pro', 'Webcam 4K60 profissional para streaming e criação de conteúdo.', 329.99, 299.99, 'assets/images/products/streaming/elgato-facecam-pro.jpg', 8, 9, 'Elgato', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-20 17:40:43'),
(104, 'Logitech StreamCam', 'Webcam Full HD otimizada para Twitch, YouTube e videoconferência.', 149.99, 129.99, 'assets/images/products/streaming/logitech-streamcam.jpg', 18, 9, 'Logitech', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-19 15:08:05'),
(105, 'Logitech Brio 4K', 'Webcam Ultra HD 4K com HDR e foco automático.', 229.99, 199.99, 'assets/images/products/streaming/logitech-brio-4k.jpg', 9, 9, 'Logitech', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-22 20:30:18'),
(106, 'HyperX QuadCast S', 'Microfone USB RGB para streamers e criadores de conteúdo.', 179.99, 159.99, 'assets/images/products/streaming/hyperx-quadcast-s.jpg', 19, 9, 'HyperX', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-22 20:30:18'),
(107, 'Elgato Wave:3', 'Microfone USB premium com tecnologia Clipguard.', 169.99, 149.99, 'assets/images/products/streaming/elgato-wave-3.jpg', 14, 9, 'Elgato', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-20 17:40:43'),
(108, 'Rode NT-USB+', 'Microfone USB de qualidade de estúdio para gravação e streaming.', 199.99, 179.99, 'assets/images/products/streaming/rode-nt-usb-plus.jpg', 13, 9, 'Rode', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-19 15:08:05'),
(109, 'Shure MV7+', 'Microfone híbrido USB/XLR utilizado por streamers e podcasters profissionais.', 329.99, 299.99, 'assets/images/products/streaming/shure-mv7-plus.jpg', 9, 9, 'Shure', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-20 17:40:43'),
(110, 'Elgato Cam Link 4K', 'Captura HDMI em 4K para câmaras DSLR e consolas.', 129.99, 109.99, 'assets/images/products/streaming/elgato-cam-link-4k.jpg', 15, 9, 'Elgato', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-19 15:08:05'),
(111, 'OBSBOT Tiny 2', 'Câmara 4K inteligente com seguimento automático por IA.', 399.99, 369.99, 'assets/images/products/streaming/obsbot-tiny-2.jpg', 7, 9, 'OBSBOT', NULL, 'PC', 'Streaming', NULL, NULL, NULL, 0, 0, 1, '2026-07-19 15:08:05', '2026-07-20 17:40:43'),
(112, 'DualSense PS5 Branco', 'Comando sem fios oficial para PlayStation 5 com resposta háptica e gatilhos adaptativos.', 74.99, 69.99, 'assets/images/products/controllers/dualsense-ps5-white.jpg', 23, 7, 'Sony', NULL, 'PS5, PC', 'Comando', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:51:41', '2026-07-22 12:20:52'),
(113, 'DualSense Midnight Black', 'Comando DualSense em acabamento Midnight Black com tecnologia háptica e microfone integrado.', 79.99, 72.99, 'assets/images/products/controllers/dualsense-midnight-black.jpg', 20, 7, 'Sony', NULL, 'PS5, PC', 'Comando', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(114, 'DualSense Edge', 'Comando profissional PlayStation com botões personalizáveis, perfis e módulos substituíveis.', 239.99, 219.99, 'assets/images/products/controllers/dualsense-edge.jpg', 9, 7, 'Sony', NULL, 'PS5, PC', 'Comando profissional', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:51:41', '2026-07-22 20:30:18'),
(115, 'Xbox Wireless Carbon Black', 'Comando sem fios Xbox com design ergonómico, gatilhos texturizados e ligação Bluetooth.', 64.99, 59.99, 'assets/images/products/controllers/xbox-wireless-carbon-black.jpg', 24, 7, 'Microsoft', NULL, 'Xbox Series X|S, Xbox One, PC', 'Comando', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(116, 'Xbox Wireless Velocity Green', 'Comando Xbox sem fios em acabamento Velocity Green, compatível com consola e computador.', 69.99, 62.99, 'assets/images/products/controllers/xbox-wireless-velocity-green.jpg', 18, 7, 'Microsoft', NULL, 'Xbox Series X|S, Xbox One, PC', 'Comando', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(117, 'Xbox Elite Series 2', 'Comando profissional Xbox com componentes ajustáveis, perfis personalizados e estojo de transporte.', 179.99, 159.99, 'assets/images/products/controllers/xbox-elite-series-2.jpg', 12, 7, 'Microsoft', NULL, 'Xbox Series X|S, Xbox One, PC', 'Comando profissional', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(118, 'Nintendo Switch Pro Controller', 'Comando oficial Nintendo com ligação sem fios, vibração HD e autonomia prolongada.', 69.99, 64.99, 'assets/images/products/controllers/nintendo-switch-pro-controller.jpg', 19, 7, 'Nintendo', NULL, 'Nintendo Switch', 'Comando', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(119, '8BitDo Ultimate Bluetooth', 'Comando sem fios com base de carregamento, botões traseiros e perfis configuráveis.', 79.99, 69.99, 'assets/images/products/controllers/8bitdo-ultimate-bluetooth.jpg', 17, 7, '8BitDo', NULL, 'Nintendo Switch, PC', 'Comando', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(120, 'Razer Wolverine V2 Pro', 'Comando competitivo sem fios com botões adicionais e elevada capacidade de personalização.', 269.99, 239.99, 'assets/images/products/controllers/razer-wolverine-v2-pro.jpg', 8, 7, 'Razer', NULL, 'PS5, PC', 'Comando profissional', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(121, 'SCUF Reflex FPS', 'Comando de alto desempenho para jogos competitivos com paddles traseiros e resposta rápida.', 289.99, 269.99, 'assets/images/products/controllers/scuf-reflex-fps.jpg', 7, 7, 'SCUF', NULL, 'PS5, PC', 'Comando profissional', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:51:41', '2026-07-20 17:51:41'),
(122, 'AMD Ryzen 7 9800X3D', 'Processador gaming de elevado desempenho com tecnologia 3D V-Cache.', 579.99, 549.99, 'assets/images/products/components/amd-ryzen-7-9800x3d.jpg', 7, 8, 'AMD', NULL, 'PC', 'Processador', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:52:48', '2026-07-21 17:29:23'),
(123, 'Intel Core i7-14700K', 'Processador Intel de alto desempenho para gaming, multitarefa e criação de conteúdos.', 449.99, 419.99, 'assets/images/products/components/intel-core-i7-14700k.jpg', 12, 8, 'Intel', NULL, 'PC', 'Processador', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(124, 'NVIDIA GeForce RTX 5070', 'Placa gráfica preparada para gaming em alta resolução, ray tracing e tecnologias de inteligência artificial.', 749.99, 719.99, 'assets/images/products/components/nvidia-rtx-5070.jpg', 8, 8, 'NVIDIA', NULL, 'PC', 'Placa gráfica', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(125, 'NVIDIA GeForce RTX 5080', 'Placa gráfica premium para jogos em 4K, ray tracing avançado e elevada performance.', 1299.99, 1249.99, 'assets/images/products/components/nvidia-rtx-5080.jpg', 5, 8, 'NVIDIA', NULL, 'PC', 'Placa gráfica', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(126, 'AMD Radeon RX 9070 XT', 'Placa gráfica AMD de elevado desempenho para gaming em alta resolução.', 899.99, 849.99, 'assets/images/products/components/amd-radeon-rx-9070-xt.jpg', 7, 8, 'AMD', NULL, 'PC', 'Placa gráfica', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(127, 'ASUS ROG STRIX B650E-F', 'Motherboard gaming para processadores AMD com suporte DDR5, PCIe e conectividade avançada.', 319.99, 299.99, 'assets/images/products/components/asus-rog-strix-b650e-f.jpg', 11, 8, 'ASUS', NULL, 'PC', 'Motherboard', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(128, 'Corsair Vengeance DDR5 32GB', 'Kit de memória DDR5 de 32 GB desenvolvido para gaming e aplicações exigentes.', 139.99, 119.99, 'assets/images/products/components/corsair-vengeance-ddr5-32gb.jpg', 20, 8, 'Corsair', NULL, 'PC', 'Memória RAM', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(129, 'Samsung 990 Pro 2TB', 'SSD NVMe de 2 TB com velocidades elevadas para jogos, aplicações e sistema operativo.', 189.99, 169.99, 'assets/images/products/components/samsung-990-pro-2tb.jpg', 18, 8, 'Samsung', NULL, 'PC', 'Armazenamento SSD', NULL, NULL, NULL, 1, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(130, 'Corsair RM850x', 'Fonte de alimentação modular de 850 W com elevada eficiência e funcionamento silencioso.', 169.99, 149.99, 'assets/images/products/components/corsair-rm850x.jpg', 14, 8, 'Corsair', NULL, 'PC', 'Fonte de alimentação', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(131, 'Noctua NH-D15 G2', 'Cooler premium para processador com elevada capacidade de refrigeração e baixo ruído.', 159.99, 144.99, 'assets/images/products/components/noctua-nh-d15-g2.jpg', 13, 8, 'Noctua', NULL, 'PC', 'Refrigeração', NULL, NULL, NULL, 0, 0, 1, '2026-07-20 17:52:48', '2026-07-20 17:52:48'),
(132, 'Ghost of Tsushima Director\'s Cut', 'Aventura de ação em mundo aberto passada no Japão feudal, acompanhando Jin Sakai na defesa da ilha de Tsushima.', 69.99, 59.99, 'assets/images/products/games/ghost-of-tsushima.jpg', 15, 13, 'PlayStation Studios', '2024-05-16', 'PC, PS5', 'Ação, Aventura', 'Sucker Punch Productions', 'Sony Interactive Entertainment', '18', 0, 0, 1, '2026-07-22 15:54:58', '2026-07-22 15:54:58'),
(133, 'God of War Ragnarök', 'Kratos e Atreus enfrentam os acontecimentos do Ragnarök numa aventura épica inspirada na mitologia nórdica.', 79.99, 69.99, 'assets/images/products/games/god-of-war-ragnarok.jpg', 15, 13, 'PlayStation Studios', '2022-11-09', 'PC, PS4, PS5', 'Ação, Aventura', 'Santa Monica Studio', 'Sony Interactive Entertainment', '18', 1, 0, 1, '2026-07-22 15:54:58', '2026-07-22 15:54:58'),
(134, 'Gran Turismo 7', 'Simulador de condução com centenas de automóveis, circuitos, personalização e modos competitivos.', 79.99, 69.99, 'assets/images/products/games/gran-turismo-7.jpg', 18, 13, 'PlayStation Studios', '2022-03-04', 'PS4, PS5', 'Corridas, Simulação', 'Polyphony Digital', 'Sony Interactive Entertainment', '3', 0, 0, 1, '2026-07-22 15:54:58', '2026-07-22 15:54:58'),
(135, 'Horizon Forbidden West', 'Aloy explora novas regiões, enfrenta máquinas colossais e procura uma forma de salvar o mundo.', 69.99, 59.99, 'assets/images/products/games/horizon-forbidden-west.jpg', 16, 13, 'PlayStation Studios', '2022-02-18', 'PC, PS4, PS5', 'Ação, RPG, Aventura', 'Guerrilla Games', 'Sony Interactive Entertainment', '16', 0, 0, 1, '2026-07-22 15:54:58', '2026-07-22 15:54:58'),
(136, 'Marvel\'s Spider-Man 2', 'Peter Parker e Miles Morales unem forças para proteger Nova Iorque de novos inimigos e ameaças.', 79.99, 69.99, 'assets/images/products/games/spiderman-2.jpg', 20, 13, 'PlayStation Studios', '2023-10-20', 'PC, PS5', 'Ação, Aventura', 'Insomniac Games', 'Sony Interactive Entertainment', '16', 1, 0, 1, '2026-07-22 15:54:58', '2026-07-22 15:54:58'),
(137, 'The Last of Us Part I', 'Joel e Ellie atravessam os Estados Unidos numa história intensa de sobrevivência, perda e esperança.', 79.99, 69.99, 'assets/images/products/games/the-last-of-us-part-1.jpg', 14, 13, 'PlayStation Studios', '2022-09-02', 'PC, PS5', 'Ação, Aventura, Sobrevivência', 'Naughty Dog', 'Sony Interactive Entertainment', '18', 0, 0, 1, '2026-07-22 15:54:58', '2026-07-22 15:54:58');

-- --------------------------------------------------------

--
-- Estrutura da tabela `product_platforms`
--

CREATE TABLE `product_platforms` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `plataforma` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_promocional` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `product_platforms`
--

INSERT INTO `product_platforms` (`id`, `product_id`, `plataforma`, `preco`, `preco_promocional`, `stock`) VALUES
(1, 9, 'PS5', 79.99, NULL, 25),
(2, 9, 'Xbox Series X|S', 79.99, NULL, 20),
(3, 9, 'PC', 69.99, 59.99, 20),
(7, 10, 'PS5', 79.99, NULL, 40),
(8, 11, 'PS5', 69.99, NULL, 20),
(9, 11, 'PC', 59.99, NULL, 10),
(10, 12, 'PS5', 74.99, NULL, 15),
(11, 12, 'Xbox Series X|S', 74.99, NULL, 10),
(12, 12, 'PC', 64.99, NULL, 10),
(16, 28, 'PS5', 69.99, 49.99, 20),
(17, 28, 'PC', 59.99, 39.99, 15),
(18, 29, 'PS5', 79.99, 49.99, 22),
(19, 30, 'PS5', 79.99, 39.99, 12),
(20, 30, 'PC', 59.99, 34.99, 8),
(21, 31, 'PS5', 79.99, 54.99, 16),
(22, 31, 'PC', 69.99, 49.99, 12),
(23, 32, 'PS5', 79.99, 64.99, 18),
(24, 32, 'PC', 69.99, 59.99, 14),
(25, 33, 'PS5', 79.99, 69.99, 18),
(26, 33, 'PC', 69.99, 59.99, 12),
(27, 34, 'PS5', 69.99, NULL, 40),
(28, 35, 'PS5', 79.99, 59.99, 15),
(29, 35, 'PC', 69.99, 54.99, 9),
(30, 36, 'PS5', 39.99, NULL, 25),
(31, 36, 'PC', 39.99, NULL, 20),
(32, 132, 'PC', 59.99, 54.99, 15),
(33, 132, 'PS5', 69.99, 59.99, 15),
(34, 133, 'PC', 69.99, 59.99, 15),
(35, 133, 'PS4', 59.99, 49.99, 15),
(36, 133, 'PS5', 79.99, 69.99, 15),
(37, 134, 'PS4', 59.99, 49.99, 15),
(38, 134, 'PS5', 79.99, 69.99, 15),
(39, 135, 'PC', 59.99, 49.99, 15),
(40, 135, 'PS4', 49.99, 39.99, 15),
(41, 135, 'PS5', 69.99, 59.99, 15),
(42, 136, 'PC', 69.99, 59.99, 13),
(43, 136, 'PS5', 79.99, 69.99, 15),
(44, 137, 'PC', 59.99, 49.99, 15),
(45, 137, 'PS5', 79.99, 69.99, 15);

-- --------------------------------------------------------

--
-- Estrutura da tabela `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `classificacao` tinyint(4) NOT NULL,
  `comentario` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo_utilizador` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `telefone` varchar(20) DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `password`, `tipo_utilizador`, `telefone`, `morada`, `cidade`, `codigo_postal`, `criado_em`) VALUES
(2, 'Carolina', 'teste12@gmail.com', '$2y$10$aJJaNH1A0LEiSphiZPAIOe/M.b0SmD.B0OWxPlOvXRtlEHutOMzu.', 'cliente', '+351923748762', 'madeira', 'Funchal', '9000-000', '2026-07-20 21:35:52'),
(3, 'PetraAdmin', 'teste0303@gmail.com', '$2y$10$aZh6asVXzltSCl0X7EdLLev2wQxEW4nw85yZ4UU5ltzH3ITbIMTgy', 'admin', '123456789', 'madeira', 'madeira', '0000000', '2026-07-21 17:48:11'),
(4, 'Kiko', 'kiko1234@gmail.com', '$2y$10$u45C9pXLd9VdY3kGiJPiUuWSWNNDMhv9dIhM4V.yAC3fysqRLVjDG', 'cliente', '924589623', 'funchal', 'funchal', '9000-000', '2026-07-22 20:29:26'),
(5, 'efgewsf', 'sefsefse@gmail.ck', '$2y$10$GDR6JjbluFo3WpwM12KHSerC3GiLNapNQLds9rF9QVi1jsm/Z6Pwq', 'cliente', '44444444444', 'warefgwerf', 'wefewf', '44444444', '2026-07-22 21:57:34');

-- --------------------------------------------------------

--
-- Estrutura da tabela `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `criado_em`) VALUES
(8, 3, 136, '2026-07-22 21:21:18'),
(9, 3, 126, '2026-07-22 21:21:21'),
(11, 5, 122, '2026-07-22 22:03:37'),
(12, 4, 132, '2026-07-22 22:12:05');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices para tabela `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_users` (`user_id`);

--
-- Índices para tabela `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_orders` (`order_id`),
  ADD KEY `fk_order_items_products` (`product_id`);

--
-- Índices para tabela `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_categories` (`categoria_id`);

--
-- Índices para tabela `product_platforms`
--
ALTER TABLE `product_platforms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_platform` (`product_id`,`plataforma`);

--
-- Índices para tabela `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_review_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_reviews_products` (`product_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wishlist_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_wishlist_products` (`product_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT de tabela `product_platforms`
--
ALTER TABLE `product_platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de tabela `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`categoria_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Limitadores para a tabela `product_platforms`
--
ALTER TABLE `product_platforms`
  ADD CONSTRAINT `fk_product_platforms_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlist_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
