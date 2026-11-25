-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 25, 2026 at 05:12 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kainstein`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `account_id` int UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `account_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `email`, `password`, `role`, `created_at`, `updated_at`, `account_name`) VALUES
(1, 'admin@gmail.com', '$2y$10$z5D0hfk2QHUY2tgJhh2Bk.j2QbmK9uSCuhUGa1eL0bIWAQ9sMnT3i', 'admin', '2026-11-25 00:23:50', '2026-11-25 00:47:49', 'Manager'),
(2, 'staff_1@gmail.com', '$2y$10$cIXFJbcbkyGasLrLyr9NXOD92hDNpAqwbUu3o01Eq/osSHfKwb0RK', 'staff', '2026-11-25 00:32:32', '2026-11-25 05:10:11', 'Mark Jay Capinding'),
(3, 'staff_2@gmail.com', '$2y$10$7Ds2mjg8IwP0pDevAfbhY.OgENvLGkPZWyDqOLs5w7O1ogEa3k51C', 'staff', '2026-11-25 02:06:31', '2026-11-25 05:10:13', 'Sofhia Condao');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int UNSIGNED NOT NULL,
  `customer_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int UNSIGNED NOT NULL,
  `cart_id` int UNSIGNED NOT NULL,
  `menu_id` int UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int UNSIGNED NOT NULL,
  `customer_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int UNSIGNED NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` enum('main_dishes','desserts','starter','sides','drinks') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) UNSIGNED NOT NULL,
  `is_available` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `item_name`, `category`, `image_url`, `description`, `price`, `is_available`) VALUES
(1, 'Lechon Kawali', 'main_dishes', 'https://images.unsplash.com/photo-1625477811233-044633d10dd1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Crispy deep-fried pork belly served with steamed rice and special sauce', '185.00', 'Y'),
(2, 'Pork Sisig', 'main_dishes', 'https://images.unsplash.com/photo-1646821195934-fb7ddb6166ab?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Sizzling chopped pork with onions, chili, and calamansi', '185.00', 'Y'),
(3, 'Chicken Inasal', 'main_dishes', 'https://images.unsplash.com/photo-1695505486442-8ad6e83d942d?q=80&w=880&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Grilled chicken marinated in lemongrass, calamansi, and garlic', '185.00', 'Y'),
(4, 'Beef Tapa', 'main_dishes', 'https://images.unsplash.com/photo-1660899224928-73e1fdd20545?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Tender marinated beef strips served with garlic rice and fried egg', '155.00', 'Y'),
(5, 'Pork Barbecue', 'main_dishes', 'https://images.unsplash.com/photo-1616252980327-ec70572e5df9?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Grilled pork barbecue skewers coated in sweet and savory sauce', '145.00', 'Y'),
(6, 'Fried Chicken', 'main_dishes', 'https://images.unsplash.com/photo-1764018292116-003fd7d1a0ba?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Crispy golden fried chicken served with steamed rice and gravy', '165.00', 'Y'),
(7, 'Pork Sinigang', 'main_dishes', 'https://images.unsplash.com/photo-1599665141552-09848513740c?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Tender pork simmered in a sour tamarind broth with fresh vegetables', '175.00', 'Y'),
(8, 'Beef Kare-Kare', 'main_dishes', 'https://plus.unsplash.com/premium_photo-1723708871094-2c02cf5f5394?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Slow-cooked beef and vegetables in rich peanut sauce with bagoong', '185.00', 'Y'),
(9, 'Chocolate Cake', 'desserts', 'https://images.unsplash.com/photo-1768260731727-275f15ed2556?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Moist chocolate cake topped with smooth chocolate frosting', '99.00', 'Y'),
(10, 'Strawberry Cheesecake', 'desserts', 'https://plus.unsplash.com/premium_photo-1722686461601-b2a018a4213b?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Creamy cheesecake layered with a buttery crust and fresh strawberry topping', '85.00', 'Y'),
(11, 'Chocolate Brownies', 'desserts', 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Soft and fudgy chocolate brownies with a rich cocoa flavor', '85.00', 'Y'),
(12, 'Vanilla Ice Cream', 'desserts', 'https://images.unsplash.com/photo-1560008581-09826d1de69e?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Classic creamy vanilla ice cream served with chocolate drizzle', '85.00', 'Y'),
(13, 'Chicken Nuggets', 'starter', 'https://images.unsplash.com/photo-1569691899455-88464f6d3ab1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Crispy bite-sized chicken pieces served with dipping sauce', '99.00', 'Y'),
(14, 'Onion Rings', 'starter', 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Crunchy battered onion rings served with creamy dipping sauce', '85.00', 'Y'),
(15, 'Mozzarella Sticks', 'starter', 'https://images.unsplash.com/photo-1734774924912-dcbb467f8599?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Golden-brown mozzarella cheese sticks fried until golden and crispy', '115.00', 'Y'),
(16, 'French Fries', 'sides', 'https://images.unsplash.com/photo-1598679253544-2c97992403ea?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Crispy and golden potato fries served with ketchup', '75.00', 'Y'),
(17, 'Garlic Bread', 'sides', 'https://plus.unsplash.com/premium_photo-1711752902734-a36167479983?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Toasted bread brushed with garlic butter and herbs', '65.00', 'Y'),
(18, 'Mashed Potato', 'sides', 'https://images.unsplash.com/photo-1777229987546-52ee6be1c4ca?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Creamy mashed potatoes blended with butter and milk', '65.00', 'Y'),
(19, 'Iced Coffee', 'drinks', 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Chilled coffee mixed with milk and a hint of sweetness', '89.00', 'Y'),
(20, 'Cold Chocolate', 'drinks', 'https://images.unsplash.com/photo-1584680744830-465a1dcd78e1?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Cold and creamy chocolate drink served over ice', '85.00', 'Y'),
(21, 'Strawberry Milkshake', 'drinks', 'https://images.unsplash.com/photo-1611928237590-087afc90c6fd?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Creamy milkshake blended with sweet fresh strawberries', '110.00', 'Y'),
(22, 'Lemon Iced Tea', 'drinks', 'https://images.unsplash.com/photo-1560023907-5f339617ea30?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=NHwxMjA3fDA%3D', 'Refreshing chilled tea with a light, natural lemon flavor', '65.00', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int UNSIGNED NOT NULL,
  `customer_id` int UNSIGNED NOT NULL,
  `order_type` enum('dine_in','take_out') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `order_status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `placed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `menu_id` int UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `fk_cart_customer` (`customer_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `fk_cart_items_cart` (`cart_id`),
  ADD KEY `fk_cart_items_menu` (`menu_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_menu_id` (`menu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
