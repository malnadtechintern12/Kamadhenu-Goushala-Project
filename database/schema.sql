-- ==========================================================
-- Kamadhenu Goushala Database Schema
-- MySQL 8.0+ Compatible with utf8mb4 and Foreign Keys
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `kamadhenu_goushala`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `kamadhenu_goushala`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admin_email` (`email`),
  INDEX `idx_admin_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Breeds Table
CREATE TABLE IF NOT EXISTS `breeds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `origin` VARCHAR(150) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `milk_yield` VARCHAR(100) DEFAULT NULL,
  `characteristics` TEXT DEFAULT NULL,
  `whatsapp_number` VARCHAR(50) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_breed_status` (`status`),
  INDEX `idx_breed_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cows Table
CREATE TABLE IF NOT EXISTS `cows` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tag_number` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `breed_id` INT DEFAULT NULL,
  `gender` ENUM('Female', 'Male') NOT NULL DEFAULT 'Female',
  `dob` DATE DEFAULT NULL,
  `arrival_date` DATE DEFAULT NULL,
  `health_status` ENUM('Healthy', 'Under Treatment', 'Recovering', 'Special Care', 'Elderly Care') DEFAULT 'Healthy',
  `story` TEXT DEFAULT NULL,
  `whatsapp_number` VARCHAR(50) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Active', 'Adopted', 'Transferred', 'Deceased') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_cows_breed` (`breed_id`),
  INDEX `idx_cows_status` (`status`),
  INDEX `idx_cows_health` (`health_status`),
  INDEX `idx_cows_gender` (`gender`),
  INDEX `idx_cows_created` (`created_at`),
  CONSTRAINT `fk_cows_breed` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Cow Additional Images Table
CREATE TABLE IF NOT EXISTS `cow_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cow_id` INT NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(200) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_cow_images_cow` (`cow_id`),
  CONSTRAINT `fk_cow_images_cow` FOREIGN KEY (`cow_id`) REFERENCES `cows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Seva Services Table
CREATE TABLE IF NOT EXISTS `seva` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `short_desc` VARCHAR(255) DEFAULT NULL,
  `full_desc` TEXT DEFAULT NULL,
  `suggested_amount` DECIMAL(10,2) DEFAULT 1001.00,
  `icon` VARCHAR(100) DEFAULT 'bi-heart-fill',
  `image` VARCHAR(255) DEFAULT NULL,
  `whatsapp_number` VARCHAR(50) DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_seva_slug` (`slug`),
  INDEX `idx_seva_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Donations Table
CREATE TABLE IF NOT EXISTS `donations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `donation_number` VARCHAR(50) NOT NULL UNIQUE,
  `donor_name` VARCHAR(150) NOT NULL,
  `donor_email` VARCHAR(150) NOT NULL,
  `donor_phone` VARCHAR(30) DEFAULT NULL,
  `pan_number` VARCHAR(20) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `seva_id` INT DEFAULT NULL,
  `seva_name` VARCHAR(150) DEFAULT 'General Donation',
  `message` TEXT DEFAULT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'Razorpay',
  `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
  `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
  `razorpay_signature` VARCHAR(255) DEFAULT NULL,
  `payment_status` ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_donation_email` (`donor_email`),
  INDEX `idx_donation_status` (`payment_status`),
  INDEX `idx_donation_seva` (`seva_id`),
  INDEX `idx_donation_created` (`created_at`),
  CONSTRAINT `fk_donations_seva` FOREIGN KEY (`seva_id`) REFERENCES `seva` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Sponsors Table
CREATE TABLE IF NOT EXISTS `sponsors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `pan_number` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_sponsors_email` (`email`),
  INDEX `idx_sponsors_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Sponsorships Table
CREATE TABLE IF NOT EXISTS `sponsorships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sponsor_id` INT NOT NULL,
  `cow_id` INT NOT NULL,
  `duration_months` INT NOT NULL DEFAULT 1,
  `amount` DECIMAL(10,2) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `payment_status` ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Completed',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_sponsorship_sponsor` (`sponsor_id`),
  INDEX `idx_sponsorship_cow` (`cow_id`),
  INDEX `idx_sponsorship_status` (`payment_status`),
  CONSTRAINT `fk_sponsorship_sponsor` FOREIGN KEY (`sponsor_id`) REFERENCES `sponsors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sponsorship_cow` FOREIGN KEY (`cow_id`) REFERENCES `cows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Testimonials Table
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `author_name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) DEFAULT 'Devotee / Donor',
  `message` TEXT NOT NULL,
  `rating` INT DEFAULT 5,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_testimonials_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Timeline Table
CREATE TABLE IF NOT EXISTS `timeline` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year` VARCHAR(20) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `display_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_timeline_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Product Categories Table
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_prodcat_slug` (`slug`),
  INDEX `idx_prodcat_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT DEFAULT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `whatsapp_number` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_status` (`status`),
  INDEX `idx_products_slug` (`slug`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'Cash on Delivery',
  `payment_status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
  `order_status` ENUM('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_orders_customer` (`customer_email`),
  INDEX `idx_orders_status` (`order_status`),
  INDEX `idx_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_order_items_order` (`order_id`),
  INDEX `idx_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Blog Categories Table
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_blogcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Blogs Table
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(100) DEFAULT 'Kamadhenu Seva Team',
  `status` ENUM('Draft', 'Published') DEFAULT 'Published',
  `published_at` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_blogs_category` (`category_id`),
  INDEX `idx_blogs_status` (`status`),
  INDEX `idx_blogs_slug` (`slug`),
  INDEX `idx_blogs_created` (`created_at`),
  CONSTRAINT `fk_blogs_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Events Table
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `start_time` TIME DEFAULT NULL,
  `end_time` TIME DEFAULT NULL,
  `location` VARCHAR(200) NOT NULL,
  `registration_url` VARCHAR(255) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Upcoming', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_events_status` (`status`),
  INDEX `idx_events_date` (`event_date`),
  INDEX `idx_events_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Gallery Categories Table
CREATE TABLE IF NOT EXISTS `gallery_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_galcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Gallery Table
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `display_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_gallery_category` (`category_id`),
  INDEX `idx_gallery_status` (`status`),
  INDEX `idx_gallery_created` (`created_at`),
  CONSTRAINT `fk_gallery_category` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('New', 'Read', 'Replied') DEFAULT 'New',
  `admin_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_contact_status` (`status`),
  INDEX `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `status` ENUM('Active', 'Unsubscribed') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Website Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Admin Activity Logs Table
CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT DEFAULT NULL,
  `admin_name` VARCHAR(100) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL,
  `entity_id` INT DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admin_logs_admin` (`admin_id`),
  INDEX `idx_admin_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Page Banners Table (Admin Managed Page Banners)
CREATE TABLE IF NOT EXISTS `page_banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_key` VARCHAR(50) NOT NULL UNIQUE,
  `page_name` VARCHAR(100) NOT NULL,
  `banner_image` VARCHAR(500) DEFAULT NULL,
  `badge_text` VARCHAR(150) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `subtitle` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_page_key` (`page_key`),
  INDEX `idx_banner_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

