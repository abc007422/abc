CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `email` VARCHAR(255) UNIQUE NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'admin', 'owner') NOT NULL,
    `index_number` VARCHAR(255) UNIQUE NULL,
    `access_key` VARCHAR(255) NULL, -- For admin registration
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- You could add other tables here in the future
-- For example:
-- CREATE TABLE IF NOT EXISTS `password_resets` (
--  `id` INT AUTO_INCREMENT PRIMARY KEY,
--  `email` VARCHAR(255) NOT NULL,
--  `token` VARCHAR(255) NOT NULL UNIQUE,
--  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

CREATE TABLE IF NOT EXISTS `student_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `full_name` VARCHAR(255) NULL,
    `class` VARCHAR(100) NULL,
    `date_of_birth` DATE NULL,
    `address` TEXT NULL,
    `parent_contact` VARCHAR(50) NULL,
    `exam_performance` TEXT NULL,
    `other_information` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `folders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `parent_id` INT NULL, -- For subfolders, can be NULL for top-level
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL, -- Or ON DELETE CASCADE if subfolders should be deleted
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `timetables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `grade` VARCHAR(255) NOT NULL,
    `class_name` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL UNIQUE,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `uploaded_by` INT NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for faster searching on the files table
ALTER TABLE `files` ADD INDEX `idx_original_filename` (`original_filename`);
ALTER TABLE `files` ADD INDEX `idx_subject` (`subject`);
ALTER TABLE `files` ADD INDEX `idx_year` (`year`);
-- Description is TEXT, a standard index might not be as effective or might have length limitations.
-- For TEXT fields, FULLTEXT is often better but more involved.
-- For now, we'll skip indexing `description` or assume simple LIKE is okay for its current usage.
-- If searching description becomes a bottleneck, FULLTEXT should be investigated.

CREATE TABLE IF NOT EXISTS `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `folder_id` INT NULL, -- Can be NULL if files are not in a folder or in a default "root"
    `user_id` INT NOT NULL, -- Uploader
    `subject` VARCHAR(255) NULL,
    `year` INT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL UNIQUE,
    `file_path` VARCHAR(255) NOT NULL, -- Relative path to the file
    `file_type` VARCHAR(100) NULL,
    `file_size` INT NULL, -- Store file size in bytes
    `description` TEXT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL, -- If folder is deleted, file might remain or be re-assigned
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE -- If uploader is deleted, their files are also deleted
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `date` DATE NOT NULL,
    `time` TIME NULL,
    `location` VARCHAR(255) NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
