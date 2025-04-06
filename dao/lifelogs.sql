-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2025 at 08:09 PM
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
-- Database: `lifelogs`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `user_id`, `title`, `summary`, `content`, `image_url`, `caption`, `category`, `tag`, `likes`, `dislikes`, `created_at`) VALUES
(1, 1, 'How running 10 miles a day changed my life forever', 'When addiction got a better of me, I have chosen a lifestyle which changed me forever.', 'When addiction got the best of me, I chose a lifestyle that transformed everything...', 'frontend/static-assets/images/running-guy.jpg', 'Running can change your life.', 'featured', 'lifestyle', 12, 2, '2023-12-31 23:00:00'),
(2, 2, 'Quick peek inside of my little garden', 'Who does not like to relax with the scenery of fresh vegetables you cared for.', 'Who doesn\'t like relaxing surrounded by the greenery of self-grown vegetables...', 'frontend/static-assets/images/gardening.jpg', 'My peaceful green retreat.', 'featured', 'home', 12, 2, '2023-12-10 23:00:00'),
(3, 3, 'How I beat David Goggins by eating cereal', 'Nobody believed me until I showed the results of eating better.', 'Nobody believed it until I showed them the truth about cereal power...', 'frontend/static-assets/images/david-goggings.jpg', 'Yes, cereal really helped.', 'featured', 'funny', 12, 2, '2024-01-31 23:00:00'),
(4, 4, 'How I won by playing the objective', 'Step 1, keep your mind clear and focus.', 'Step 1, keep your mind clear and focus...', 'frontend/static-assets/images/objective.jpg', 'Focus wins games.', 'latest', 'gaming', 12, 2, '2024-02-04 23:00:00'),
(5, 5, 'This cooking recipe changed my life', 'With a small amount of this secret sauce, anything is possible.', 'With a small amount of this secret sauce, anything is possible...', 'frontend/static-assets/images/gordon.jpg', 'The sauce makes the dish.', 'latest', 'cooking', 12, 2, '2024-02-07 23:00:00'),
(6, 6, 'How this Yu-Gi-Oh deck boosted my wins', 'My win percentage doubled after getting those cards.', 'My win percentage doubled after using these cards...', 'frontend/static-assets/images/yugio.jpg', 'Believe in the heart of the cards.', 'latest', 'gaming', 12, 2, '2024-02-09 23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `blog_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `blog_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 1, 'Test komentar', '2025-04-06 18:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `likes_dislikes`
--

CREATE TABLE `likes_dislikes` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_like` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes_dislikes`
--

INSERT INTO `likes_dislikes` (`id`, `blog_id`, `user_id`, `is_like`, `created_at`) VALUES
(1, 1, 1, 1, '2025-04-06 18:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(1, 'test-tag');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `bio`, `avatar_url`, `created_at`) VALUES
(1, 'johndoe', 'john@example.com', 'test', 'Fitness enthusiast', 'frontend/static-assets/images/avatars/JhonDoeLogo.png', '2025-04-06 15:44:47'),
(2, 'jenfarrah', 'jen@example.com', 'test', 'Gardening lover', 'frontend/static-assets/images/avatars/JhenifferAvatar.png', '2025-04-06 15:44:47'),
(3, 'anonymous', 'anon@example.com', 'test', 'Mysterious being', 'frontend/static-assets/images/avatars/DefaultLogo.png', '2025-04-06 15:44:47'),
(4, 'gamerx', 'gamer@example.com', 'test', 'Plays to win', 'frontend/static-assets/images/avatars/GamerAvatar.png', '2025-04-06 15:44:47'),
(5, 'chefgordon', 'chef@example.com', 'test', 'Loves cooking', 'frontend/static-assets/images/avatars/ChefGordonAvatar.png', '2025-04-06 15:44:47'),
(6, 'yami', 'yami@example.com', 'test', 'Card duelist', 'frontend/static-assets/images/avatars/YamiAvatar.png', '2025-04-06 15:44:47'),
(7, 'testuser', 'test@example.com', '$2y$12$e2pX/mfRsTfpoXUAWrah3.rOmCMGBZXgMo0d5.M8aezrj.Kl4xOV6', 'novi_avatar.jpg', 'novi_avatar.jpg', '2025-04-06 18:01:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`blog_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_id` (`blog_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `likes_dislikes`
--
ALTER TABLE `likes_dislikes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_blog` (`user_id`,`blog_id`),
  ADD KEY `blog_id` (`blog_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `likes_dislikes`
--
ALTER TABLE `likes_dislikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blogs`
--
ALTER TABLE `blogs`
  ADD CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD CONSTRAINT `blog_tags_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes_dislikes`
--
ALTER TABLE `likes_dislikes`
  ADD CONSTRAINT `likes_dislikes_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_dislikes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
